@extends('layouts.app')

@section('title', 'Speeches')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8">
        <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-4">
                <h1 class="text-4xl font-black text-slate-950">Speeches & Messages</h1>
                
                @if(request()->has('id'))
                    <a href="{{ route('speeches.index') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> View All Speeches
                    </a>
                @endif
            </div>

            <div class="mt-12 space-y-16">
                @forelse ($speeches as $speech)
                    <div class="grid grid-cols-1 gap-12 lg:grid-cols-3 items-start">
                        <div class="lg:col-span-1">
                            <div class="aspect-3/4 overflow-hidden rounded-3xl bg-slate-100 shadow-lg ring-1 ring-slate-200">
                                <img src="{{ $speech->image_url }}" alt="{{ $speech->name }}" class="h-full w-full object-cover transition duration-500">
                            </div>
                            <div class="mt-6 text-center lg:text-left">
                                @if($speech->name)
                                    <h3 class="text-2xl font-black text-slate-950">{{ $speech->name }}</h3>
                                @endif
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <div class="relative">
                                <span class="absolute -left-4 -top-8 text-8xl font-serif text-slate-100 opacity-50 select-none">"</span>
                                <h2 class="text-3xl font-black text-slate-950 relative z-10">{{ $speech->title }}</h2>
                                <div class="mt-8 prose prose-slate prose-lg max-w-none text-slate-600 leading-relaxed">
                                    {!! nl2br(e($speech->speech)) !!}
                                </div>
                                <span class="absolute -right-4 bottom-0 text-8xl font-serif text-slate-100 opacity-50 select-none">"</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">No speeches found.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
