<?php

namespace App\Services\ExternalNews;

use App\Models\ExternalNewsFetchLog;
use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class ExternalNewsRssIngestionService
{
    public function ingestFromSource(ExternalNewsSource $source): ExternalNewsFetchLog
    {
        $log = ExternalNewsFetchLog::query()->create([
            'source_id' => $source->id,
            'started_at' => now(),
            'status' => ExternalNewsFetchLog::STATUS_FAILED,
            'items_found' => 0,
            'items_created' => 0,
            'items_updated' => 0,
        ]);

        if (! $source->supportsRssOrAtom()) {
            $log->update([
                'finished_at' => now(),
                'error_message' => 'Source type is not RSS or Atom.',
            ]);

            return $log;
        }

        $endpoint = $source->endpoint_url;
        if (! is_string($endpoint) || $endpoint === '') {
            $log->update([
                'finished_at' => now(),
                'error_message' => 'Missing feed URL.',
            ]);

            return $log;
        }

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'User-Agent' => 'SwaedUAE-NewsIngest/1.0 (+https://swaeduae.ae)',
                    'Accept' => 'application/rss+xml, application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
                ])
                ->get($endpoint);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }

            $body = $response->body();
            $xml = $this->parseXml($body);
            $items = $this->extractFeedItems($xml);

            $created = 0;
            $updated = 0;

            foreach ($items as $row) {
                $hash = $this->computeImportHash(
                    $source->id,
                    $row['guid'],
                    $row['link'],
                    $row['title'],
                    $row['published_at']?->toIso8601String(),
                );

                $payload = [
                    'source_id' => $source->id,
                    'external_guid' => $row['guid'],
                    'external_url' => $row['link'],
                    'original_title' => $row['title'],
                    'original_summary' => $row['summary'],
                    'original_image_url' => $row['image_url'],
                    'original_published_at' => $row['published_at'],
                    'original_language' => $row['language'],
                    'normalized_title_en' => $row['title'],
                    'normalized_summary_en' => $row['summary'],
                    'fetched_at' => now(),
                    'import_hash' => $hash,
                ];

                $existing = ExternalNewsItem::query()->where('import_hash', $hash)->first();

                if ($existing !== null) {
                    if ($existing->status === ExternalNewsItem::STATUS_PENDING_REVIEW) {
                        $existing->fill($payload);
                        $existing->save();
                        $updated++;
                    }

                    continue;
                }

                ExternalNewsItem::query()->create(array_merge($payload, [
                    'status' => ExternalNewsItem::STATUS_PENDING_REVIEW,
                    'is_featured' => false,
                    'show_on_home' => false,
                    'show_in_media_center' => true,
                ]));
                $created++;
            }

            $log->update([
                'finished_at' => now(),
                'status' => ExternalNewsFetchLog::STATUS_SUCCESS,
                'items_found' => count($items),
                'items_created' => $created,
                'items_updated' => $updated,
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'finished_at' => now(),
                'status' => ExternalNewsFetchLog::STATUS_FAILED,
                'error_message' => Str::limit($e->getMessage(), 2000),
            ]);
        }

        return $log->fresh();
    }

    private function parseXml(string $body): SimpleXMLElement
    {
        $prev = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
            if ($xml === false) {
                throw new \RuntimeException('Invalid XML');
            }

            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    /**
     * @return list<array{guid: ?string, link: ?string, title: string, summary: ?string, image_url: ?string, published_at: ?Carbon, language: ?string}>
     */
    private function extractFeedItems(SimpleXMLElement $xml): array
    {
        $name = $xml->getName();

        if ($name === 'rss') {
            return $this->extractRssItems($xml);
        }

        if ($name === 'feed') {
            return $this->extractAtomItems($xml);
        }

        return [];
    }

    /**
     * @return list<array{guid: ?string, link: ?string, title: string, summary: ?string, image_url: ?string, published_at: ?Carbon, language: ?string}>
     */
    private function extractRssItems(SimpleXMLElement $xml): array
    {
        $out = [];
        if (! isset($xml->channel->item)) {
            return $out;
        }

        foreach ($xml->channel->item as $item) {
            $title = $this->sanitizeText((string) $item->title);
            if ($title === '') {
                continue;
            }

            $link = $this->sanitizeUrl((string) $item->link);
            $guid = isset($item->guid) ? $this->sanitizeText((string) $item->guid) : null;

            $pubDate = isset($item->pubDate) ? $this->parseDate((string) $item->pubDate) : null;
            $imageUrl = $this->rssEnclosureImage($item) ?? $this->rssMediaContentUrl($item);

            $rawBody = isset($item->description) ? (string) $item->description : '';
            $contentNamespaces = $item->getDocNamespaces(true);
            if (isset($contentNamespaces['content'])) {
                $encoded = $item->children($contentNamespaces['content'])->encoded ?? null;
                if ($encoded !== null && (string) $encoded !== '') {
                    $rawBody = (string) $encoded;
                }
            }
            $summary = $this->summarizeFromRaw($rawBody);

            $out[] = [
                'guid' => $guid,
                'link' => $link,
                'title' => $title,
                'summary' => $summary,
                'image_url' => $imageUrl,
                'published_at' => $pubDate,
                'language' => null,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{guid: ?string, link: ?string, title: string, summary: ?string, image_url: ?string, published_at: ?Carbon, language: ?string}>
     */
    private function extractAtomItems(SimpleXMLElement $xml): array
    {
        $atomNs = 'http://www.w3.org/2005/Atom';
        $out = [];

        foreach ($xml->children($atomNs)->entry as $entry) {
            $title = $this->sanitizeText((string) $entry->children($atomNs)->title);
            if ($title === '') {
                continue;
            }

            $link = null;
            foreach ($entry->children($atomNs)->link as $l) {
                $rel = (string) $l['rel'];
                if ($rel === '' || $rel === 'alternate') {
                    $link = $this->sanitizeUrl((string) $l['href']);
                    break;
                }
            }

            $id = isset($entry->children($atomNs)->id)
                ? $this->sanitizeText((string) $entry->children($atomNs)->id)
                : null;
            $summary = null;
            if (isset($entry->children($atomNs)->summary)) {
                $summary = $this->summarizeFromRaw((string) $entry->children($atomNs)->summary);
            } elseif (isset($entry->children($atomNs)->content)) {
                $summary = $this->summarizeFromRaw((string) $entry->children($atomNs)->content);
            }

            $pub = isset($entry->children($atomNs)->published)
                ? (string) $entry->children($atomNs)->published
                : (isset($entry->children($atomNs)->updated) ? (string) $entry->children($atomNs)->updated : null);
            $pubDate = $pub ? $this->parseDate($pub) : null;

            $imageUrl = $this->atomLinkImage($entry, $atomNs);

            $out[] = [
                'guid' => $id,
                'link' => $link,
                'title' => $title,
                'summary' => $summary,
                'image_url' => $imageUrl,
                'published_at' => $pubDate,
                'language' => isset($entry->attributes('http://www.w3.org/XML/1998/namespace')['lang'])
                    ? (string) $entry->attributes('http://www.w3.org/XML/1998/namespace')['lang']
                    : null,
            ];
        }

        return $out;
    }

    private function summarizeFromRaw(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        $text = $this->sanitizeText(strip_tags(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

        return $text === '' ? null : Str::limit($text, 2000);
    }

    private function rssEnclosureImage(SimpleXMLElement $item): ?string
    {
        if (! isset($item->enclosure)) {
            return null;
        }

        $type = strtolower((string) $item->enclosure['type']);
        if (str_starts_with($type, 'image/')) {
            return $this->sanitizeUrl((string) $item->enclosure['url']);
        }

        return null;
    }

    private function rssMediaContentUrl(SimpleXMLElement $item): ?string
    {
        $namespaces = $item->getDocNamespaces(true);
        $mediaNs = $namespaces['media'] ?? null;
        if ($mediaNs === null) {
            return null;
        }

        $media = $item->children($mediaNs);
        if (! isset($media->content)) {
            return null;
        }

        foreach ($media->content as $c) {
            $type = strtolower((string) $c['type']);
            if (str_starts_with($type, 'image/') || (string) $c['medium'] === 'image') {
                return $this->sanitizeUrl((string) $c['url']);
            }
        }

        return null;
    }

    private function atomLinkImage(SimpleXMLElement $entry, string $atomNs): ?string
    {
        foreach ($entry->children($atomNs)->link as $l) {
            $rel = (string) $l['rel'];
            $type = strtolower((string) $l['type']);
            if ($rel === 'enclosure' && str_starts_with($type, 'image/')) {
                return $this->sanitizeUrl((string) $l['href']);
            }
        }

        return null;
    }

    private function sanitizeText(string $value): string
    {
        return trim(Str::of($value)->replace(["\0", "\x0B"], '')->squish()->value());
    }

    private function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    private function parseDate(string $value): ?Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    public function computeImportHash(int $sourceId, ?string $guid, ?string $url, string $title, ?string $publishedAt): string
    {
        $key = $guid ?: $url ?: hash('sha256', $title.'|'.($publishedAt ?? ''));

        return hash('sha256', $sourceId.'|'.$key);
    }
}
