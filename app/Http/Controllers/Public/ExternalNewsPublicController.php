<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ExternalNewsItem;
use Illuminate\View\View;

class ExternalNewsPublicController extends Controller
{
    public function show(ExternalNewsItem $external_news_item): View
    {
        if ($external_news_item->status !== ExternalNewsItem::STATUS_PUBLISHED) {
            abort(404);
        }

        if (! $external_news_item->show_in_media_center && ! $external_news_item->show_on_home) {
            abort(404);
        }

        $external_news_item->load('source');

        return view('public.external-news-show', ['item' => $external_news_item]);
    }
}
