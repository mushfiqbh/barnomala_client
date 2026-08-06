@props([
    'slides'  => [],
    'notices' => [],
    'isSliderOnly' => false,
])

@php
    $interval = 7000;
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

<div class="relative rounded-md overflow-hidden group font-sans {{ $isSliderOnly ? 'w-full max-w-[90%] md:max-w-[86%] mx-auto h-[30vh] md:h-[65vh]' : 'lg:w-2/3 h-64 md:h-80 lg:h-96 rounded-2xl shadow-2xl' }}"
     x-data="{ currentSlide: 0, totalSlides: {{ count($slides) }}, next() { this.currentSlide = (this.currentSlide + 1) % this.totalSlides }, prev() { this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides } }"
     x-init="setInterval(() => next(), {{ $interval }})">

    @foreach($slides as $index => $slide)
        <div class="absolute inset-0 transition-opacity duration-1500 ease-in-out"
             :class="currentSlide === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0'">
            <img src="{{ is_array($slide) ? $slide['url'] : $slide->url }}"
                 class="w-full h-full object-cover transform duration-10000 ease-linear"
                 :class="currentSlide === {{ $index }} ? 'scale-110' : 'scale-100'"
                 alt="Slide">
            
            <div class="absolute bottom-0 left-0 w-full p-6 pr-16 {{ $isSliderOnly ? 'md:p-16 lg:p-24' : 'md:p-8 lg:p-12' }} md:pr-28 z-20"
                 x-show="currentSlide === {{ $index }}"
                 x-transition:enter="transition ease-out duration-700 delay-300"
                 x-transition:enter-start="opacity-0 translate-y-10"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-4xl">
                    @if(isset($slide['published_at']))
                        <div class="flex items-center gap-3 mb-2 md:mb-4">
                            <span class="bg-indigo-600 text-white text-[10px] md:text-xs font-bold px-2 md:px-3 py-0.5 md:py-1 rounded-sm uppercase tracking-widest bg-opacity-90">
                                {{ \Carbon\Carbon::parse($slide['published_at'])->format('d M Y') }}
                            </span>
                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                            <span class="text-gray-300 text-xs md:text-sm font-medium tracking-wide">Featured</span>
                        </div>
                    @endif
                    <h2 class="font-black text-white mb-2 leading-tight drop-shadow-2xl {{ $isSliderOnly ? 'text-xl md:text-3xl lg:text-5xl' : 'text-xl md:text-2xl' }}">
                        {{ $slide['title'] ?? 'Institute Update' }}
                    </h2>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Thumbnail navigation overlay (vertical, right side) --}}
    <div class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 z-30 flex flex-col items-center gap-2 p-2 md:p-3">
        <div class="flex flex-col items-center gap-2 overflow-y-auto scrollbar-hide max-h-68 md:max-h-108">
            @foreach($slides as $index => $slide)
                <button @click="currentSlide = {{ $index }}"
                    class="shrink-0 w-20 h-12 md:w-32 md:h-20 rounded-lg overflow-hidden border-2 transition-all duration-300 hover:scale-105 hover:shadow-lg focus:outline-none"
                    :class="currentSlide === {{ $index }} ? 'border-white scale-105 shadow-lg ring-2 ring-white/50' : 'border-white/30 opacity-60 hover:opacity-100'">
                    <img src="{{ is_array($slide) ? $slide['url'] : $slide->url }}"
                         class="w-full h-full object-cover"
                         alt="Go to slide {{ $index + 1 }}">
                </button>
            @endforeach
        </div>
    </div>
</div>
