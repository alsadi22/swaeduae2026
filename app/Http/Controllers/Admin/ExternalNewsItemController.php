<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalNewsItem;
use App\Models\ExternalNewsSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExternalNewsItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExternalNewsItem::class);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in([
                ExternalNewsItem::STATUS_PENDING_REVIEW,
                ExternalNewsItem::STATUS_APPROVED,
                ExternalNewsItem::STATUS_PUBLISHED,
                ExternalNewsItem::STATUS_REJECTED,
            ])],
            'source_id' => ['nullable', 'integer', 'exists:external_news_sources,id'],
        ]);

        $query = ExternalNewsItem::query()
            ->with('source')
            ->orderByDesc('fetched_at');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (! empty($validated['source_id'])) {
            $query->where('source_id', (int) $validated['source_id']);
        }

        $items = $query->paginate(25)->withQueryString();
        $sources = ExternalNewsSource::query()->orderBy('name')->get();

        return view('admin.external-news.items.index', [
            'items' => $items,
            'sources' => $sources,
            'filters' => [
                'status' => $validated['status'] ?? '',
                'source_id' => $validated['source_id'] ?? '',
            ],
        ]);
    }

    public function edit(ExternalNewsItem $external_news_item): View
    {
        $this->authorize('update', $external_news_item);

        $external_news_item->load('source');

        return view('admin.external-news.items.edit', ['item' => $external_news_item]);
    }

    public function update(Request $request, ExternalNewsItem $external_news_item): RedirectResponse
    {
        $this->authorize('update', $external_news_item);

        $data = $request->validate([
            'normalized_title_en' => ['nullable', 'string', 'max:500'],
            'normalized_title_ar' => ['nullable', 'string', 'max:500'],
            'normalized_summary_en' => ['nullable', 'string', 'max:5000'],
            'normalized_summary_ar' => ['nullable', 'string', 'max:5000'],
            'local_feature_image' => ['nullable', 'string', 'max:2048'],
            'is_featured' => ['boolean'],
            'show_on_home' => ['boolean'],
            'show_in_media_center' => ['boolean'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['show_in_media_center'] = $request->boolean('show_in_media_center');

        $external_news_item->update($data);

        return redirect()
            ->route('admin.external-news-items.edit', $external_news_item)
            ->with('status', __('External news item saved.'));
    }

    public function approve(Request $request, ExternalNewsItem $external_news_item): RedirectResponse
    {
        $this->authorize('update', $external_news_item);

        if ($external_news_item->status !== ExternalNewsItem::STATUS_PENDING_REVIEW) {
            return back()->withErrors(['status' => __('Only pending items can be approved.')]);
        }

        $external_news_item->update([
            'status' => ExternalNewsItem::STATUS_APPROVED,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', __('Item approved. Publish when ready.'));
    }

    public function publish(Request $request, ExternalNewsItem $external_news_item): RedirectResponse
    {
        $this->authorize('update', $external_news_item);

        if (! in_array($external_news_item->status, [
            ExternalNewsItem::STATUS_PENDING_REVIEW,
            ExternalNewsItem::STATUS_APPROVED,
        ], true)) {
            return back()->withErrors(['status' => __('This item cannot be published from its current state.')]);
        }

        $external_news_item->update([
            'status' => ExternalNewsItem::STATUS_PUBLISHED,
            'published_at' => now(),
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', __('Item published on the site (per visibility flags).'));
    }

    public function reject(Request $request, ExternalNewsItem $external_news_item): RedirectResponse
    {
        $this->authorize('update', $external_news_item);

        if ($external_news_item->status === ExternalNewsItem::STATUS_PUBLISHED) {
            return back()->withErrors(['status' => __('Unpublish before rejecting.')]);
        }

        $external_news_item->update([
            'status' => ExternalNewsItem::STATUS_REJECTED,
            'reviewed_by_user_id' => $request->user()->id,
            'reviewed_at' => now(),
            'published_at' => null,
        ]);

        return back()->with('status', __('Item rejected.'));
    }

    public function unpublish(ExternalNewsItem $external_news_item): RedirectResponse
    {
        $this->authorize('update', $external_news_item);

        if ($external_news_item->status !== ExternalNewsItem::STATUS_PUBLISHED) {
            return back()->withErrors(['status' => __('Item is not published.')]);
        }

        $external_news_item->update([
            'status' => ExternalNewsItem::STATUS_APPROVED,
            'published_at' => null,
        ]);

        return back()->with('status', __('Item unpublished (remains approved).'));
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', ExternalNewsItem::class);

        $data = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer', 'exists:external_news_items,id'],
            'bulk_action' => ['required', 'string', Rule::in(['approve', 'reject', 'publish'])],
        ]);

        $ids = $data['item_ids'];
        $action = $data['bulk_action'];

        $items = ExternalNewsItem::query()->whereIn('id', $ids)->get();
        $count = 0;

        foreach ($items as $item) {
            if (! $request->user()->can('update', $item)) {
                continue;
            }
            if ($action === 'approve' && $item->status === ExternalNewsItem::STATUS_PENDING_REVIEW) {
                $item->update([
                    'status' => ExternalNewsItem::STATUS_APPROVED,
                    'reviewed_by_user_id' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
                $count++;
            }
            if ($action === 'publish' && in_array($item->status, [
                ExternalNewsItem::STATUS_PENDING_REVIEW,
                ExternalNewsItem::STATUS_APPROVED,
            ], true)) {
                $item->update([
                    'status' => ExternalNewsItem::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'reviewed_by_user_id' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
                $count++;
            }
            if ($action === 'reject' && $item->status !== ExternalNewsItem::STATUS_PUBLISHED) {
                $item->update([
                    'status' => ExternalNewsItem::STATUS_REJECTED,
                    'reviewed_by_user_id' => $request->user()->id,
                    'reviewed_at' => now(),
                    'published_at' => null,
                ]);
                $count++;
            }
        }

        return back()->with('status', __(':count items updated.', ['count' => $count]));
    }
}
