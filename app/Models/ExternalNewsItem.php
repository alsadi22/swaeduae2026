<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalNewsItem extends Model
{
    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'source_id',
        'external_guid',
        'external_url',
        'original_title',
        'original_summary',
        'original_image_url',
        'original_published_at',
        'original_language',
        'normalized_title_en',
        'normalized_title_ar',
        'normalized_summary_en',
        'normalized_summary_ar',
        'local_feature_image',
        'status',
        'is_featured',
        'show_on_home',
        'show_in_media_center',
        'import_hash',
        'fetched_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'original_published_at' => 'datetime',
            'fetched_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'show_on_home' => 'boolean',
            'show_in_media_center' => 'boolean',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ExternalNewsSource::class, 'source_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeVisibleOnHome($query)
    {
        return $query->published()->where('show_on_home', true);
    }

    public function scopeVisibleInMediaCenter($query)
    {
        return $query->published()->where('show_in_media_center', true);
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    public function publicDetailUrl(): string
    {
        return route('media.external.show', $this);
    }

    public function absolutePublicUrl(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $base = route('media.external.show', ['external_news_item' => $this], true);
        $sep = str_contains($base, '?') ? '&' : '?';

        return $base.$sep.'lang='.rawurlencode($locale);
    }

    public function titleForLocale(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->normalized_title_ar
                ?? $this->normalized_title_en
                ?? $this->original_title;
        }

        return $this->normalized_title_en
            ?? $this->normalized_title_ar
            ?? $this->original_title;
    }

    public function summaryForLocale(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'ar') {
            return $this->normalized_summary_ar
                ?? $this->normalized_summary_en
                ?? $this->original_summary;
        }

        return $this->normalized_summary_en
            ?? $this->normalized_summary_ar
            ?? $this->original_summary;
    }

    public function featureImageUrl(): ?string
    {
        if ($this->local_feature_image) {
            return $this->local_feature_image;
        }

        return $this->original_image_url;
    }
}
