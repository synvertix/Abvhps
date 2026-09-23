<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for the home hero slides (website + mobile API).
 *
 * Slides come from the `home_sliders` table. Because that table has no admin screen yet and is
 * empty on a fresh install, the hero would otherwise never rotate — so when no active slide exists
 * we fall back to a short set of built-in devotional slides.
 */
class HeroSlides
{
    /**
     * @return array<int, array{id: int|string, title: string, subtitle: string, image_url: ?string, cta_label: ?string, cta_url: ?string}>
     */
    public static function forHome(): array
    {
        $rows = DB::table('home_sliders')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($rows->isEmpty()) {
            return static::defaults();
        }

        return $rows->map(fn ($s) => [
            'id'        => $s->id,
            'title'     => (string) $s->title,
            'subtitle'  => (string) $s->subtitle,
            'image_url' => !empty($s->image_path) ? asset('storage/' . $s->image_path) : null,
            'cta_label' => !empty($s->cta_label) ? (string) $s->cta_label : null,
            'cta_url'   => !empty($s->cta_url) ? (string) $s->cta_url : null,
        ])->all();
    }

    /**
     * Built-in slides shown when the admin has not configured any.
     * Wording only restates ABVHPS's stated objectives (temples, Goshalas, Annapurna meals, children's literacy).
     */
    public static function defaults(): array
    {
        return [
            [
                'id'        => 'default-1',
                'title'     => 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
                'subtitle'  => 'Preserving Sanathana Dharma and Empowering Communities',
                'image_url' => null,
                'cta_label' => 'Know Our Story',
                'cta_url'   => route('about'),
            ],
            [
                'id'        => 'default-2',
                'title'     => 'Serve with Devotion',
                'subtitle'  => 'Help us care for temples, protect Goshalas, share Annapurna meals and support children\'s literacy across every Grama Panchayat.',
                'image_url' => null,
                'cta_label' => 'Make a Donation',
                'cta_url'   => route('donations.grid'),
            ],
            [
                'id'        => 'default-3',
                'title'     => 'Become Part of the Seva Family',
                'subtitle'  => 'Join as a member or volunteer and walk with us in service to Dharma and to society.',
                'image_url' => null,
                'cta_label' => 'Become a Member',
                'cta_url'   => route('membership.form'),
            ],
        ];
    }
}
