<?php

namespace App\Support;

use App\Models\CmsPage;

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
}
