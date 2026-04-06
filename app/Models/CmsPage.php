<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPage extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Slugs served by InstitutionalPageController; excluded from “latest from CMS” on the home page.
     *
     * @var list<string>
     */
    public const INSTITUTIONAL_SLUGS = [
        'about',
        'leadership',
        'programs',
        'events',
        'media',
        'partners',
        'faq',
        'terms',
        'privacy',
        'cookies',
        'youth-councils',
        'gallery',
    ];

    protected $fillable = [
        'slug',
        'locale',
        'title',
        'meta_description',
        'og_image',
        'excerpt',
        'body',
        'status',
        'published_at',
        'author_id',
        'show_on_home',
        'show_on_programs',
        'show_on_media',
        'show_in_gallery',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_on_home' => 'boolean',
            'show_on_programs' => 'boolean',
            'show_on_media' => 'boolean',
            'show_in_gallery' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public static function findPublished(string $slug, ?string $locale = null): ?self
    {
        $locale ??= app()->getLocale();

        return static::query()
            ->where('slug', $slug)
            ->forLocale($locale)
            ->published()
            ->first();
    }

    /**
     * Canonical public path for this row (institutional slugs use fixed routes; others use /page/{slug}).
     */
    public function publicUrl(): string
    {
        $routeBySlug = [
            'about' => 'about',
            'leadership' => 'leadership',
            'programs' => 'programs.index',
            'events' => 'events.index',
            'media' => 'media.index',
            'partners' => 'partners',
            'faq' => 'faq',
            'terms' => 'legal.terms',
            'privacy' => 'legal.privacy',
            'cookies' => 'legal.cookies',
            'youth-councils' => 'youth-councils',
            'gallery' => 'gallery',
        ];

        if (isset($routeBySlug[$this->slug])) {
            return route($routeBySlug[$this->slug]);
        }

        return route('cms.page', ['slug' => $this->slug]);
    }

    /**
     * Turn a stored og_image value or config path into an absolute URL for meta tags.
     */
    public static function resolveShareImageUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null) {
            return null;
        }

        $v = trim($pathOrUrl);
        if ($v === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $v)) {
            return $v;
        }

        return url($v);
    }

    public function resolvedOgImageUrl(): ?string
    {
        $fromPage = self::resolveShareImageUrl($this->og_image);
        if ($fromPage !== null) {
            return $fromPage;
        }

        return self::resolveShareImageUrl(config('swaeduae.default_og_image_url'));
    }

    /**
     * Fully qualified public URL for this row, including ?lang= for the active (or given) UI locale.
     */
    public function absolutePublicUrl(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $base = $this->publicUrl();
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.'lang='.rawurlencode($locale);
    }

    public function isPubliclyVisible(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED || $this->published_at === null) {
            return false;
        }

        if ($this->published_at->isFuture()) {
            return false;
        }

        $live = self::findPublished($this->slug, $this->locale);

        return $live !== null && $live->is($this);
    }
}
