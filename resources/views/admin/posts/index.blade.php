@extends('layouts.admin')

@section('title', 'Posts')

@push('header_actions')
    <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 text-sm font-semibold shadow-sm shadow-indigo-500/30 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        <i class="fas fa-plus text-xs"></i> New Post
    </a>
@endpush

@push('styles')
<style>
    @keyframes rowFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .post-row {
        animation: rowFadeIn 0.35s ease-out both;
        transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .post-row:hover {
        background-color: #f8fafc;
    }
    .post-row:hover .row-thumb {
        transform: scale(1.05);
        box-shadow: 0 8px 20px -8px rgba(79, 70, 229, 0.45);
    }
    .post-row:hover .row-arrow {
        opacity: 1;
        transform: translateX(0);
    }
    .post-row:hover .row-title {
        color: #4f46e5;
    }
    .row-arrow {
        opacity: 0;
        transform: translateX(-6px);
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .row-thumb {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .row-title {
        transition: color 0.2s ease;
    }
    .tab-pill {
        transition: all 0.2s ease;
    }
    .tab-pill:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
@php
    $typeStyles = [
        'notice'         => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'ring' => 'ring-amber-200/60'],
        'news'           => ['bg' => 'bg-sky-50',     'text' => 'text-sky-700',     'ring' => 'ring-sky-200/60'],
        'document'       => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'ring' => 'ring-indigo-200/60'],
        'admission_form' => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'ring' => 'ring-rose-200/60'],
        'other_forms'    => ['bg' => 'bg-pink-50',    'text' => 'text-pink-700',    'ring' => 'ring-pink-200/60'],
        'class_routine'  => ['bg' => 'bg-teal-50',    'text' => 'text-teal-700',    'ring' => 'ring-teal-200/60'],
        'exam_routine'   => ['bg' => 'bg-cyan-50',    'text' => 'text-cyan-700',    'ring' => 'ring-cyan-200/60'],
        'syllabus'       => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200/60'],
        'magazine'       => ['bg' => 'bg-fuchsia-50', 'text' => 'text-fuchsia-700', 'ring' => 'ring-fuchsia-200/60'],
        'board_result'   => ['bg' => 'bg-violet-50',  'text' => 'text-violet-700',  'ring' => 'ring-violet-200/60'],
    ];
@endphp
<div class="space-y-6">
    <!-- Tabs -->
    <nav class="flex flex-wrap gap-2 p-1 rounded-lg bg-white border border-slate-200/70 shadow-sm">
        <a href="{{ route('admin.posts.index') }}"
           class="tab-pill inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold {{ $tab === 'all' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm shadow-indigo-500/30' : 'text-slate-600 hover:bg-slate-100' }}">
            <i class="fas fa-layer-group text-sm"></i>
            All
            <span class="px-1.5 py-0.5 font-bold {{ $tab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                {{ $posts->total() }}
            </span>
        </a>
        @foreach($postTypes as $key => $label)
            <a href="{{ route('admin.posts.index', ['tab' => $key]) }}"
               class="tab-pill inline-flex items-center gap-2 px-4 py-2 font-semibold {{ $tab === $key ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm shadow-indigo-500/30' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="fas {{ \App\Models\Post::typeIcon($key) }} text-sm"></i>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <!-- Posts Card -->
    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead>
                    <tr class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold border-b border-slate-100">
                        <th class="px-6 py-4">Post</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Published</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80">
                    @forelse($posts as $post)
                        @php
                            $style = $typeStyles[$post->type] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-200/60'];
                            $typeName = $post->source_type === 'document' ? \App\Models\Post::typeLabel($post->type) : ucfirst($post->type);
                            $imageUrl = $post->image_json['url'] ?? null;
                        @endphp
                        <tr class="post-row cursor-pointer group"
                            onclick="window.location='{{ route('admin.posts.edit', $post) }}'"
                            style="animation-delay: {{ $loop->index * 30 }}ms">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="row-thumb relative w-12 h-12 rounded-xl overflow-hidden ring-1 ring-slate-200/60 shrink-0 flex items-center justify-center">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas {{ \App\Models\Post::typeIcon($post->type) }} text-indigo-400"></i>
                                        @endif
                                        @if($post->is_urgent)
                                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-rose-500 ring-2 ring-white" title="Urgent"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="row-title font-semibold text-slate-800 truncate max-w-xs">{{ $post->title }}</p>
                                            <i class="fas fa-arrow-right row-arrow text-xs text-indigo-500"></i>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
                                            @if($post->source_type === 'document' && $post->class_label)
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fas fa-graduation-cap text-[10px]"></i>
                                                    {{ $post->class_label }}
                                                </span>
                                                <span class="text-slate-300">•</span>
                                            @endif
                                            @if($post->artifacts->count())
                                                <span class="inline-flex items-center gap-1">
                                                    <i class="fas fa-paperclip text-[10px]"></i>
                                                    {{ $post->artifacts->count() }} {{ Str::plural('file', $post->artifacts->count()) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold ring-1 ring-inset {{ $style['bg'] }} {{ $style['text'] }} {{ $style['ring'] }}">
                                    <i class="fas {{ \App\Models\Post::typeIcon($post->type) }} text-[10px]"></i>
                                    {{ $typeName }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-700 font-medium">{{ $post->published_at?->format('M d, Y') ?? '—' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $post->published_at?->diffForHumans() ?? 'Unpublished' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($post->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Live
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline"
                                          onsubmit="event.stopPropagation(); return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                                @click.stop
                                                type="submit"
                                                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Delete">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <i class="fas fa-inbox text-2xl text-slate-300"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-700 font-semibold">No posts found</p>
                                        <p class="text-sm text-slate-400 mt-1">Get started by creating a new post.</p>
                                    </div>
                                    <a href="{{ route('admin.posts.create') }}" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold transition-colors">
                                        <i class="fas fa-plus text-xs"></i> Create Post
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
