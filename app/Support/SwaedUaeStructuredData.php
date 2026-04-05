<?php

namespace App\Support;

use App\Models\CmsPage;
use App\Models\Event;
use App\Models\ExternalNewsItem;
use Illuminate\Support\Str;

final class SwaedUaeStructuredData
{
    /**
     * @return array<string, mixed>|null
     */
    public static function publicLayoutGraph(bool $isAdminCmsPreview): ?array
    {
        if ($isAdminCmsPreview) {
            return null;
        }

        $org = [
            '@type' => 'NGO',
            '@id' => url('/').'#organization',
            'name' => __('SwaedUAE'),
            'url' => url('/'),
        ];

        $logo = CmsPage::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
        if ($logo) {
            $org['logo'] = $logo;
        }

        $domain = config('swaeduae.domain');
        if (is_string($domain) && $domain !== '') {
            $org['identifier'] = [
                '@type' => 'PropertyValue',
                'name' => 'domain',
                'value' => $domain,
            ];
        }

        $website = [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => __('SwaedUAE'),
            'publisher' => ['@id' => url('/').'#organization'],
            'inLanguage' => ['en', 'ar'],
        ];

        return [
            '@context' => 'https://schema.org',
            '@graph' => [$org, $website],
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbGraph(array $items): array
    {
        $elements = [];
        foreach ($items as $i => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * Schema.org Event for public calendar and volunteer opportunity detail pages.
     *
     * @return array<string, mixed>
     */
    public static function publicEventForJsonLd(Event $event): array
    {
        $event->loadMissing('organization');

        $localeQ = PublicLocale::query();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->titleForLocale(),
            'startDate' => $event->event_starts_at->toIso8601String(),
            'endDate' => $event->event_ends_at->toIso8601String(),
            'url' => route('events.show', array_merge(['event' => $event], $localeQ), true),
        ];

        if ($event->organization !== null) {
            $data['organizer'] = [
                '@type' => 'Organization',
                'name' => $event->organization->nameForLocale(),
            ];
        }

        if ($event->latitude !== null && $event->longitude !== null) {
            $data['location'] = [
                '@type' => 'Place',
                'geo' => [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                ],
            ];
        }

        return $data;
    }

    /**
     * Schema.org NewsArticle for external (syndicated) news detail pages.
     *
     * @return array<string, mixed>
     */
    public static function externalNewsArticleForJsonLd(ExternalNewsItem $item, string $pageUrl): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $item->titleForLocale(),
            'url' => $pageUrl,
            'mainEntityOfPage' => $pageUrl,
        ];

        if ($item->published_at !== null) {
            $data['datePublished'] = $item->published_at->toIso8601String();
        }

        $data['dateModified'] = $item->updated_at->toIso8601String();

        $sum = $item->summaryForLocale();
        if (is_string($sum) && $sum !== '') {
            $data['description'] = Str::limit(strip_tags($sum), 500);
        }

        $img = $item->featureImageUrl();
        if (is_string($img) && $img !== '') {
            $data['image'] = [$img];
        }

        $data['publisher'] = [
            '@type' => 'Organization',
            'name' => __('SwaedUAE'),
            'url' => url('/'),
        ];

        return $data;
    }
}
