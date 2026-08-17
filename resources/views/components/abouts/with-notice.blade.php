@props([
    'options' => [],
    'notices' => [],
])

@php
    $aboutImageOption = \App\Models\Option::where('option_key', 'institute.about.image_json')->first();
    $aboutImageUrl = $aboutImageOption ? (json_decode($aboutImageOption->option_value, true)['url'] ?? asset('images/about-image.webp')) : asset('images/about-image.webp');
@endphp

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="max-w-[90%] mx-auto md:px-6 lg:px-8 font-bn reveal">
    <div class="relative overflow-hidden bg-white rounded-lg shadow-[0_32px_120px_-20px_rgba(30,41,59,0.08)] group border border-slate-100">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 group-hover:opacity-50 transition duration-1000"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-30 group-hover:opacity-50 transition duration-1000"></div>

        <div class="relative flex flex-col lg:flex-row items-stretch">
            {{-- Content Side --}}
            <div class="lg:w-2/3 p-2 md:p-8 flex flex-col justify-center">
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-black text-slate-900 leading-[1.1] tracking-tight">
                            {{ $options['institute.about.title'] ?? 'আমাদের প্রতিষ্ঠান সম্পর্কে' }}
                        </h2>
                        <div class="w-20 h-2 bg-indigo-600 rounded-full mt-6 group-hover:w-32 transition-all duration-500"></div>
                    </div>

                    <div class="space-y-6">
                        <div class="relative group/text">
                            <div class="h-48 overflow-y-auto scrollbar-hide text-slate-600 text-lg leading-relaxed font-medium prose prose-slate max-w-none">
                                {!! $options['institute.about.text'] ?? 'আমাদের শিক্ষা প্রতিষ্ঠান একটি ঐতিহ্যবাহী বিদ্যাপীঠ। দীর্ঘ পথচলায় আমরা অসংখ্য মেধাবী শিক্ষার্থী উপহার দিয়েছি যারা দেশ ও দশের কল্যাণে নিয়োজিত।' !!}
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 h-4 bg-linear-to-t from-white to-transparent pointer-events-none opacity-0 group-hover/text:opacity-100 transition-opacity"></div>
                        </div>

                        <div class="w-full flex justify-end">
                            <a href="{{ route('about') }}"
                                class="items-center text-sm font-black text-indigo-600 border rounded-md p-3 transition-colors uppercase tracking-widest hover:bg-indigo-500 hover:text-white">
                                {{ $options['institute.about.button_text'] ?? 'Read More' }} <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notice Panel Side --}}
            <div class="lg:w-1/3 relative overflow-hidden">
                <div class="flex flex-col h-full bg-gray-50/50">
                    <div class="bg-accent text-white p-5 font-bold flex justify-between items-center shadow-lg relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <span class="text-xl flex items-center gap-3 relative z-10 tracking-wide">
                            <span class="w-1.5 h-6 bg-yellow-400 rounded-full inline-block shadow-[0_0_10px_rgba(250,204,21,0.5)]"></span>
                            সর্বশেষ নোটিশ
                        </span>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="p-2 space-y-3 overflow-y-auto max-h-96 scrollbar-thin scrollbar-thumb-indigo-100 scrollbar-track-transparent">
                            @foreach($notices as $notice)
                                <div class="group/item border border-gray-100 bg-white rounded-xl px-4 py-3 hover:bg-indigo-50/80 hover:border-indigo-100 transition-all duration-300">
                                    <a href="{{ route('notices.show', $notice->id) }}" class="flex gap-4 items-start">
                                        <div class="bg-white text-indigo-700 w-22 h-8 text-sm rounded-xl shrink-0 flex items-center justify-center font-bold shadow-sm border border-indigo-50 transition-all duration-300 transform group-hover/item:-translate-y-1">
                                            <span class="text-base leading-none">{{ formatDateBN($notice->published_at, 'day') }} - {{ formatDateBN($notice->published_at, 'month') }}</span>
                                        </div>
                                        <div class="flex-1">
                                            @if($notice->is_urgent)
                                                <div class="text-[10px] font-black text-rose-600 mb-1 flex items-center gap-1">
                                                    <span class="relative flex h-2 w-2">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                                    </span>
                                                    জরুরি
                                                </div>
                                            @endif
                                            <h4 class="text-gray-800 font-bold text-sm leading-snug line-clamp-2 group-hover/item:text-indigo-700 transition-colors">{{ $notice->title }}</h4>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
