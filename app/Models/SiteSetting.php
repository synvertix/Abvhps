<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    public static function get($key, $default = null)
    {
        return Cache::rememberForever("site_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value, $group = 'general')
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
        Cache::forget("site_setting_{$key}");

        // Batch 2 — Realtime invalidation for home-affecting settings.
        // Dispatch ONLY for 'contact_phone' in this first proof.
        // DB is committed and cache is cold before this fires.
        // ShouldDispatchAfterCommit on HomeContentUpdated guarantees
        // zero broadcasts if an outer transaction rolls back.
        if ($key === 'contact_phone') {
            event(new \App\Events\HomeContentUpdated());
        }

        return $setting;
    }

    public static function getAllSettings()
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Get the configured official WhatsApp contact number.
     * Default: +91 9989980055
     */
    public static function getWhatsAppNumber(): string
    {
        return static::get('whatsapp_number', '+91 9989980055');
    }

    /**
     * Get canonical/normalized international format for WhatsApp URL (e.g. 919989980055).
     * Strips +, spaces, hyphens, brackets and ensures country code.
     */
    public static function getNormalizedWhatsAppNumber(): string
    {
        $raw = static::getWhatsAppNumber();
        $digits = preg_replace('/[^0-9]/', '', (string)$raw);

        if (strlen($digits) === 10) {
            return '91' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return '91' . substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return !empty($digits) ? $digits : '919989980055';
    }

    /**
     * Generate canonical wa.me URL for the configured WhatsApp number.
     */
    public static function getWhatsAppUrl(?string $message = null): string
    {
        $normalized = static::getNormalizedWhatsAppNumber();
        $url = 'https://wa.me/' . $normalized;

        if (!empty($message)) {
            $url .= '?text=' . urlencode($message);
        }

        return $url;
    }

    /**
     * Get structured supporting partners list with backward compatibility.
     *
     * @return array<int, array{id: string, name: string, logo_path: ?string, order: int}>
     */
    public static function getSupportingPartners(): array
    {
        $json = static::get('homepage_sponsors_structured');
        if ($json) {
            $decoded = is_string($json) ? json_decode($json, true) : $json;
            if (is_array($decoded) && count($decoded) > 0) {
                usort($decoded, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
                return array_values($decoded);
            }
        }

        // Backward compatibility fallback from homepage_sponsors_list
        $rawList = static::get('homepage_sponsors_list');
        $defaultNames = ['Synvertix Technologies', 'MMP', 'MMS', 'MMA', 'Taskly'];
        $names = [];

        if ($rawList) {
            if (is_string($rawList)) {
                $jsonParsed = json_decode($rawList, true);
                if (is_array($jsonParsed)) {
                    $names = array_filter(array_map('trim', $jsonParsed));
                } else {
                    $lines = preg_split('/\r\n|\r|\n/', $rawList);
                    $names = array_values(array_filter(array_map('trim', $lines)));
                }
            }
        }

        if (empty($names)) {
            $names = $defaultNames;
        }

        $structured = [];
        $order = 1;
        foreach ($names as $name) {
            $structured[] = [
                'id'        => 'partner_' . substr(md5($name . $order), 0, 10),
                'name'      => $name,
                'logo_path' => null,
                'order'     => $order++,
            ];
        }

        return $structured;
    }

    /**
     * Store structured supporting partners list.
     *
     * @param array<int, array{id?: string, name: string, logo_path?: ?string, order?: int}> $partners
     */
    public static function setSupportingPartners(array $partners): void
    {
        $normalized = [];
        $order = 1;
        foreach ($partners as $p) {
            if (empty($p['name']) || !is_string($p['name'])) {
                continue;
            }
            $name = trim($p['name']);
            if ($name === '') {
                continue;
            }
            $normalized[] = [
                'id'        => !empty($p['id']) ? (string)$p['id'] : 'partner_' . uniqid(),
                'name'      => $name,
                'logo_path' => !empty($p['logo_path']) ? (string)$p['logo_path'] : null,
                'order'     => isset($p['order']) ? (int)$p['order'] : $order,
            ];
            $order++;
        }

        // Stable sort with index fallback
        $indexed = [];
        $i = 0;
        foreach ($normalized as $item) {
            $indexed[] = ['item' => $item, 'index' => $i++];
        }
        usort($indexed, function ($a, $b) {
            $orderA = $a['item']['order'] ?? 0;
            $orderB = $b['item']['order'] ?? 0;
            if ($orderA === $orderB) {
                return $a['index'] <=> $b['index'];
            }
            return $orderA <=> $orderB;
        });
        $normalized = array_column($indexed, 'item');

        // Reset sequence integers 1..N
        $seq = 1;
        foreach ($normalized as &$item) {
            $item['order'] = $seq++;
        }
        unset($item);

        static::set('homepage_sponsors_structured', json_encode(array_values($normalized)));

        // Keep homepage_sponsors_list synced for any legacy consumers
        $namesOnly = implode("\n", array_column($normalized, 'name'));
        static::set('homepage_sponsors_list', $namesOnly);
    }

    /**
     * Supported social media platform definitions.
     *
     * @return array<string, array{key: string, name: string, short_name: string, aria_label: string}>
     */
    public static function getSupportedSocialPlatforms(): array
    {
        return [
            'facebook' => [
                'key'        => 'social_facebook_url',
                'name'       => 'Facebook',
                'short_name' => 'Facebook',
                'aria_label' => 'ABVHPS on Facebook',
            ],
            'instagram' => [
                'key'        => 'social_instagram_url',
                'name'       => 'Instagram',
                'short_name' => 'Instagram',
                'aria_label' => 'ABVHPS on Instagram',
            ],
            'youtube' => [
                'key'        => 'social_youtube_url',
                'name'       => 'YouTube',
                'short_name' => 'YouTube',
                'aria_label' => 'ABVHPS on YouTube',
            ],
            'x' => [
                'key'        => 'social_x_url',
                'name'       => 'X / Twitter',
                'short_name' => 'X / Twitter',
                'aria_label' => 'ABVHPS on X',
            ],
            'linkedin' => [
                'key'        => 'social_linkedin_url',
                'name'       => 'LinkedIn',
                'short_name' => 'LinkedIn',
                'aria_label' => 'ABVHPS on LinkedIn',
            ],
            'whatsapp' => [
                'key'        => 'social_whatsapp_url',
                'name'       => 'WhatsApp',
                'short_name' => 'WhatsApp',
                'aria_label' => 'ABVHPS on WhatsApp',
            ],
            'telegram' => [
                'key'        => 'social_telegram_url',
                'name'       => 'Telegram',
                'short_name' => 'Telegram',
                'aria_label' => 'ABVHPS on Telegram',
            ],
        ];
    }

    /**
     * Get active, validated social media links for public display.
     * Returns only platforms with valid, non-empty, safe URLs.
     *
     * @return array<string, array{id: string, name: string, short_name: string, url: string, aria_label: string}>
     */
    public static function getActiveSocialLinks(): array
    {
        $platforms = static::getSupportedSocialPlatforms();
        $active = [];

        foreach ($platforms as $id => $meta) {
            $rawUrl = static::get($meta['key']);

            if (!empty($rawUrl) && is_string($rawUrl)) {
                $trimmed = trim($rawUrl);
                if ($trimmed === '') {
                    continue;
                }

                // Strictly require https:// scheme and valid URL format
                if (!str_starts_with(strtolower($trimmed), 'https://') || !filter_var($trimmed, FILTER_VALIDATE_URL)) {
                    continue;
                }

                // WhatsApp specific strict domain check (wa.me or api.whatsapp.com)
                if ($id === 'whatsapp') {
                    if (!str_starts_with(strtolower($trimmed), 'https://wa.me/') && !str_starts_with(strtolower($trimmed), 'https://api.whatsapp.com/')) {
                        continue;
                    }
                }

                // Reject unsafe schemes / payloads
                if (preg_match('/^(javascript|data|file|vbscript):/i', $trimmed)) {
                    continue;
                }

                $active[$id] = [
                    'id'         => $id,
                    'name'       => $meta['name'],
                    'short_name' => $meta['short_name'],
                    'url'        => $trimmed,
                    'aria_label' => $meta['aria_label'],
                ];
            }
        }

        return $active;
    }
}
