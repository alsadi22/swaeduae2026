<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_mission_en',
        'hero_mission_ar',
        'hero_subline_en',
        'hero_subline_ar',
        'header_logo_path',
    ];

    /**
     * Row for admin edit form (creates an empty row on first open).
     */
    public static function current(): self
    {
        $row = static::query()->first();
        if ($row !== null) {
            return $row;
        }

        return static::query()->create([]);
    }

    /**
     * Published site branding / hero (no side effects — avoids creating rows on public traffic).
     */
    public static function forPublic(): ?self
    {
        return static::query()->first();
    }

    public function heroMission(): string
    {
        $key = app()->getLocale() === 'ar' ? 'hero_mission_ar' : 'hero_mission_en';
        $custom = $this->attributes[$key] ?? null;

        return filled($custom) ? (string) $custom : (string) __('site.hero_mission');
    }

    public function heroSubline(): string
    {
        $key = app()->getLocale() === 'ar' ? 'hero_subline_ar' : 'hero_subline_en';
        $custom = $this->attributes[$key] ?? null;

        return filled($custom) ? (string) $custom : (string) __('site.hero_subline');
    }

    public function headerLogoPublicUrl(): ?string
    {
        $path = $this->header_logo_path;
        if (! filled($path)) {
            return null;
        }
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
