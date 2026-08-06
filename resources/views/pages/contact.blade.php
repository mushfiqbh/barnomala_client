@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
@php
    $instituteName = $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $phone = $options['institute.contact.phone'] ?? ($options['phone'] ?? '01234-567890');
    $email = $options['institute.contact.email'] ?? ($options['email'] ?? 'info@barnomala.com');
    $address = $options['institute.contact.address'] ?? 'Institute address goes here';
    $mapLink = trim((string) ($options['institute.contact.map_link'] ?? ''));
    $mapEmbedUrl = '';

    if ($mapLink !== '') {
        $mapEmbedUrl = (str_contains($mapLink, '/embed') || str_contains($mapLink, 'output=embed'))
            ? $mapLink
            : (str_contains($mapLink, '?') ? $mapLink . '&output=embed' : $mapLink . '?output=embed');
    } else {
        $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($address) . '&output=embed';
    }
@endphp

<section class="py-16">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-4xl bg-[#002147] p-8 text-white shadow-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-300">Contact</p>
                <h1 class="mt-4 text-4xl font-black">{{ $instituteName }}</h1>
                <div class="mt-8 space-y-6 text-sm leading-7 text-slate-200">
                    <div>
                        <p class="font-bold uppercase tracking-[0.25em] text-slate-400">Address</p>
                        <p class="mt-2">{{ $address }}</p>
                    </div>
                    <div>
                        <p class="font-bold uppercase tracking-[0.25em] text-slate-400">Phone</p>
                        <p class="mt-2">{{ $phone }}</p>
                    </div>
                    <div>
                        <p class="font-bold uppercase tracking-[0.25em] text-slate-400">Email</p>
                        <p class="mt-2">{{ $email }}</p>
                    </div>
                </div>
                <p class="mt-8 rounded-3xl bg-white/5 p-5 text-sm leading-7 text-slate-200">Send us a message using the form and Laravel will handle the submission with normal validation errors and Blade flash feedback.</p>
            </div>

            <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Send Message</p>
                <h2 class="mt-4 text-3xl font-black text-slate-950">We'd love to hear from you</h2>

                <form action="{{ route('contact.submit') }}" method="POST" class="mt-8 space-y-6">
                    @csrf
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950 focus:bg-white">
                            @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950 focus:bg-white">
                            @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Subject</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950 focus:bg-white">
                        @error('subject')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-bold uppercase tracking-[0.2em] text-slate-600">Message</label>
                        <textarea id="message" name="message" rows="6" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-950 focus:bg-white">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    
                    @if(session('error'))
                        <p class="text-sm font-medium text-rose-600">{{ session('error') }}</p>
                    @endif

                    <button type="submit" class="rounded-2xl bg-slate-950 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Map Section -->
        <div class="mt-16" id="map">
        <!-- Responsive Map Container -->
        <div class="w-full max-w-4xl mx-auto">
            <div class="relative w-full h-0 pb-[68.29%] rounded-lg overflow-hidden shadow-lg">
                <iframe class="absolute top-0 left-0 w-full h-full"
                    src="{{ $mapEmbedUrl }}"
                    frameborder="0" scrolling="no" marginheight="0" marginwidth="0" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</section>
@endsection
