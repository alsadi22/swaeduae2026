<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ExternalNewsSource extends Model
{
    public const TYPE_RSS = 'rss';

    public const TYPE_ATOM = 'atom';

    public const TYPE_API = 'api';

    public const TYPE_HTML_PARSER = 'html_parser';

    public const TYPE_MANUAL = 'manual';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'endpoint_url',
        'website_url',
        'source_logo',
        'label_en',
        'label_ar',
        'is_active',
        'fetch_interval_minutes',
        'priority',
        'parser_config',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'parser_config' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ExternalNewsSource $source): void {
            if ($source->slug === null || $source->slug === '') {
                $source->slug = Str::slug($source->name);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExternalNewsItem::class, 'source_id');
    }

    public function fetchLogs(): HasMany
    {
        return $this->hasMany(ExternalNewsFetchLog::class, 'source_id');
    }

    public function latestFetchLog(): HasOne
    {
        return $this->hasOne(ExternalNewsFetchLog::class, 'source_id')->latestOfMany('finished_at');
    }

    public function labelForLocale(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return $locale === 'ar' ? $this->label_ar : $this->label_en;
    }

    public function supportsRssOrAtom(): bool
    {
        return in_array($this->type, [self::TYPE_RSS, self::TYPE_ATOM], true);
    }
}
