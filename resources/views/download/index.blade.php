@extends('layouts.app')

@section('title', 'Download')

@section('content')
<section class="py-12 md:py-16">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Resources</p>
        <h1 class="mt-4 text-4xl md:text-5xl font-black text-slate-950">Download Center</h1>
        
        @if(empty($grouped))
            <div class="mt-12 rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center text-slate-500">
                <i class="fas fa-folder-open text-4xl text-slate-300 mb-3"></i>
                <p class="font-medium text-slate-600">No downloads are available right now.</p>
                <p class="text-sm mt-1">Please check back later.</p>
            </div>
        @else
            {{-- Quick Category Navigation --}}
            <nav class="mt-10 flex flex-wrap gap-2">
                @foreach($grouped as $typeKey => $bucket)
                    <a href="#cat-{{ $typeKey }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                        <i class="fas {{ $bucket['icon'] }} text-indigo-500"></i>
                        {{ $bucket['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-12 space-y-12">
                @foreach($grouped as $typeKey => $bucket)
                    <section id="cat-{{ $typeKey }}" class="scroll-mt-24">
                        <div class="flex items-center gap-3 border-b border-slate-200 pb-4">
                            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="fas {{ $bucket['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-950">{{ $bucket['label'] }}</h2>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    {{ $bucket['items']->count() }} {{ \Illuminate\Support\Str::plural('item', $bucket['items']->count()) }}
                                </p>
                            </div>
                        </div>

                        <ul class="mt-5 space-y-3">
                            @foreach($bucket['items'] as $download)
                                <li class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($download->class_label)
                                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-600">
                                                        {{ $download->class_label }}
                                                    </span>
                                                @endif
                                                <span class="text-xs font-semibold text-slate-400">
                                                    Published {{ optional($download->published_at)->format('d M Y') }}
                                                </span>
                                            </div>
                                            <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $download->title }}</h3>
                                            @if($download->description)
                                                <p class="mt-1.5 text-sm text-slate-600 line-clamp-2">{{ $download->description }}</p>
                                            @endif
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2 md:flex-col md:items-stretch md:w-56 shrink-0">
                                            @forelse($download->artifacts as $artifact)
                                                @php
                                                    $ext = strtolower(pathinfo($artifact->file_name, PATHINFO_EXTENSION));
                                                    $icon = match(true) {
                                                        $ext === 'pdf' => 'fa-file-pdf',
                                                        in_array($ext, ['doc','docx'], true) => 'fa-file-word',
                                                        in_array($ext, ['xls','xlsx'], true) => 'fa-file-excel',
                                                        in_array($ext, ['ppt','pptx'], true) => 'fa-file-powerpoint',
                                                        in_array($ext, ['zip','rar'], true) => 'fa-file-archive',
                                                        in_array($ext, ['jpg','jpeg','png','gif','webp'], true) => 'fa-file-image',
                                                        default => 'fa-file-alt',
                                                    };
                                                    $sizeKb = $artifact->file_size / 1024;
                                                    $sizeText = $sizeKb >= 1024 ? number_format($sizeKb / 1024, 2) . ' MB' : number_format($sizeKb, 1) . ' KB';
                                                @endphp
                                                <a href="/storage/{{ ltrim($artifact->file_path, '/') }}"
                                                   target="_blank" rel="noreferrer"
                                                   class="group flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">
                                                    <span class="flex items-center gap-3 min-w-0">
                                                        <i class="fas {{ $icon }} text-rose-500"></i>
                                                        <span class="truncate" title="{{ $artifact->file_name }}">{{ $artifact->file_name }}</span>
                                                    </span>
                                                    <span class="flex items-center gap-2 shrink-0">
                                                        <span class="text-[10px] uppercase tracking-wider text-slate-400">{{ $sizeText }}</span>
                                                        <i class="fas fa-download text-indigo-500 group-hover:translate-y-0.5 transition-transform"></i>
                                                    </span>
                                                </a>
                                            @empty
                                                <span class="text-xs italic text-slate-400">No file attached.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
