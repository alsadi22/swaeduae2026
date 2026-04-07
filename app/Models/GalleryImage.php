<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    protected $fillable = [
        'sort_order',
        'path',
        'alt_text_en',
        'alt_text_ar',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function publicUrl(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function altForLocale(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        if ($locale === 'ar' && filled($this->alt_text_ar)) {
            return (string) $this->alt_text_ar;
        }
        if (filled($this->alt_text_en)) {
            return (string) $this->alt_text_en;
        }

        return (string) __('Gallery photo');
    }
}
