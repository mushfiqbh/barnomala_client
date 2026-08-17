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
            {{-- Image Side (shown first on desktop) --}}
            <div class="lg:w-2/5 relative overflow-hidden group/image lg:order-1">
                <img src="{{ $aboutImageUrl }}" alt="About Us Image"
                     class="w-full h-full object-cover relative z-10 transition-all duration-1000 group-hover:scale-110"
                     style="clip-path: polygon(10% 0%, 100% 0%, 100% 100%, 0% 100%);">
            </div>

            {{-- Content Side --}}
            <div class="lg:w-3/5 p-8 flex flex-col justify-center lg:order-2">
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
        </div>
    </div>
</div>
