<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Option;
use App\Services\ThemeService;

trait BuildsPublicPageData
{
    protected function getPublicPageData(): array
    {
        $this->incrementVisitorCount();

        $options = Option::all()->pluck('value', 'option_key')->toArray();

        return [
            'options' => $options,
            'navigationItems' => $this->getNavigationItems(),
            'importantLinks' => $this->getImportantLinks($options),
            'theme' => app(ThemeService::class),
        ];
    }

    protected function getNavigationItems(): array
    {
        $items = app(ThemeService::class)->navigationItems();

        // Add Branches dropdown from options JSON (only if there are branches other than the main www branch)
        $branches = Option::where('option_key', 'institute.branches.json')->value('option_value');
        $branches = $branches ? json_decode($branches, true) : [];
        $extraBranches = collect($branches)->filter(fn($b) => ($b['sub_domain'] ?? 'www') !== 'www')->values();

        if ($extraBranches->isNotEmpty()) {
            $children = collect($branches)->map(function ($branch) {
                $subDomain = $branch['sub_domain'] ?? '';
                return [
                    'label' => $branch['name'] ?? $subDomain,
                    'url' => url('/' . $subDomain),
                ];
            })->toArray();

            $items[] = [
                'label' => 'Branches',
                'url' => '#',
                'children' => $children,
            ];
        }

        return $items;
    }

    protected function getImportantLinks(array $options): array
    {
        $fallback = config('navigation.important_links', []);
        $raw = $options['institute.links.important_json'] ?? null;

        if (empty($raw)) {
            return $fallback;
        }

        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        if (!is_array($raw)) {
            return $fallback;
        }

        $links = array_values(array_filter(array_map(function ($item) {
            $title = trim((string) ($item['title'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));

            if ($title === '' || $url === '') {
                return null;
            }

            return [
                'title' => $title,
                'url' => $url,
            ];
        }, $raw)));

        return !empty($links) ? $links : $fallback;
    }

    protected function incrementVisitorCount(): void
    {
        $sessionKey = 'has_visited_site';

        if (!session()->has($sessionKey)) {
            $option = Option::where('option_key', 'site.visitor_count')->first();

            if (!$option) {
                Option::create([
                    'option_key' => 'site.visitor_count',
                    'option_value' => '1',
                    'value_type' => 'integer',
                ]);
            } else {
                $option->option_value = (int) $option->option_value + 1;
                $option->save();
            }

            session()->put($sessionKey, true);
        }
    }
}
