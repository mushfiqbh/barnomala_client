<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Post;
use Illuminate\View\View;

class DownloadController extends Controller
{
    use BuildsPublicPageData;

    public function index(): View
    {
        $downloads = Post::downloads()->with('artifacts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();

        // Group active downloads by type while preserving the canonical TYPES order.
        $grouped = [];
        foreach (array_keys(Post::POST_TYPES) as $typeKey) {
            $items = $downloads->where('type', $typeKey)->values();
            if ($items->isNotEmpty()) {
                $grouped[$typeKey] = [
                    'label' => Post::POST_TYPES[$typeKey],
                    'icon'  => Post::typeIcon($typeKey),
                    'items' => $items,
                ];
            }
        }

        return view('download.index', array_merge($this->getPublicPageData(), [
            'grouped'  => $grouped,
            'downloads' => $downloads,
        ]));
    }
}
