<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Post;
use Illuminate\View\View;

class NewsController extends Controller
{
    use BuildsPublicPageData;

    public function index(): View
    {
        $news = Post::news()->where('is_active', true)
            ->latest('published_at')
            ->paginate(12);

        return view('news.index', array_merge($this->getPublicPageData(), compact('news')));
    }

    public function show(Post $news): View
    {
        if ($news->source_type !== 'news' || !$news->is_active) {
            abort(404);
        }

        $news->load('artifacts');
        
        $recentNews = Post::news()->where('is_active', true)
            ->where('id', '!=', $news->id)
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('news.show', array_merge($this->getPublicPageData(), compact('news', 'recentNews')));
    }
}
