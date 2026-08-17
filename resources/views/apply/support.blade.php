@extends('layouts.app')

@section('title', 'Support')

@section('content')
@php
    $instituteName = $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $phone = $options['institute.contact.phone'] ?? ($options['phone'] ?? '');
    $phone2 = $options['institute.contact.phone_extra'] ?? '';
    $email = $options['institute.contact.email'] ?? ($options['email'] ?? '');
    $address = $options['institute.contact.address'] ?? '';
    $whatsapp = $options['institute.social.whatsapp'] ?? ($options['institute.contact.phone'] ?? '');
    if (str_starts_with($whatsapp, '01')) {
        $whatsapp = '+88' . $whatsapp;
    }
    $waDigits = preg_replace('/[^0-9]/', '', $whatsapp);
    $supportTopics = [
        [
            'title' => 'Applying for admission',
            'desc' => 'How to fill the online application form, choose class, group and submit your details.',
            'url' => route('apply.new'),
            'cta' => 'Start an application',
            'icon' => 'fa-file-circle-plus',
            'tint' => 'bg-indigo-50 text-indigo-600',
        ],
        [
            'title' => 'Finding my application',
            'desc' => 'Search your submitted applications with your phone number and date of birth.',
            'url' => route('apply.applications'),
            'cta' => 'Search applications',
            'icon' => 'fa-folder-open',
            'tint' => 'bg-emerald-50 text-emerald-600',
        ],
        [
            'title' => 'Paying the admission fee',
            'desc' => 'Complete your fee payment through mobile banking and get it verified by the office.',
            'url' => route('apply.payment'),
            'cta' => 'Continue payment',
            'icon' => 'fa-credit-card',
            'tint' => 'bg-amber-50 text-amber-600',
        ],
        [
            'title' => 'Editing or updating details',
            'desc' => 'Found a mistake? Re-open your submitted application from My Applications and update it.',
            'url' => route('apply.applications'),
            'cta' => 'Edit application',
            'icon' => 'fa-pen-to-square',
            'tint' => 'bg-sky-50 text-sky-600',
        ],
    ];
@endphp

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Admission Portal</p>
                <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Support</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">
                    Get help with your application, payment or anything related to admission at {{ $instituteName }}.
                </p>
            </div>
            <a href="{{ route('apply.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left"></i> Back to Apply
            </a>
        </div>

        {{-- Contact cards --}}
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
            <div class="rounded-4xl bg-[#002147] p-8 text-white shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-amber-300">
                    <i class="fas fa-phone text-xl"></i>
                </div>
                <h2 class="mt-5 text-lg font-black">Call the Admission Office</h2>
                <p class="mt-3 text-sm leading-7 text-slate-300">Speak directly with our staff during office hours.</p>
                <div class="mt-5 space-y-3">
                    @if($phone)
                        <a href="tel:{{ $phone }}" class="flex items-center justify-between rounded-2xl bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                            <span>{{ $phone }}</span>
                            <i class="fas fa-phone-volume text-amber-300"></i>
                        </a>
                    @endif
                    @if($phone2)
                        <a href="tel:{{ $phone2 }}" class="flex items-center justify-between rounded-2xl bg-white/5 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                            <span>{{ $phone2 }}</span>
                            <i class="fas fa-phone-volume text-amber-300"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-black text-slate-950">Message us on WhatsApp</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        Send your application number and query, and we will respond as soon as possible.
                    </p>
                    @if($waDigits)
                        <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500">
                            <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    @endif
                </div>

                <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <i class="fas fa-envelope-open-text text-xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-black text-slate-950">Email us</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">Prefer email? Write to us with your application details.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if($email)
                            <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 transition hover:bg-slate-100">
                                <i class="fas fa-envelope"></i> {{ $email }}
                            </a>
                        @endif
                        <a href="{{ route('contact.index') }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                            <i class="fas fa-paper-plane"></i> Contact Form
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Common topics --}}
        <div class="mt-14">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Common topics</p>
            <h2 class="mt-3 text-2xl font-black text-slate-950">Popular questions &amp; quick actions</h2>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                @foreach($supportTopics as $topic)
                    <a href="{{ $topic['url'] }}" class="group card-hover flex items-start gap-4 rounded-4xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $topic['tint'] }}">
                            <i class="fas {{ $topic['icon'] }} text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950">{{ $topic['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-6 text-slate-500">{{ $topic['desc'] }}</p>
                            <span class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-indigo-600">
                                {{ $topic['cta'] }}
                                <i class="fas fa-arrow-right transition-transform duration-300 group-hover:translate-x-1"></i>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Visit office --}}
        @if($address)
            <div class="mt-10 flex flex-col items-center justify-between gap-4 rounded-4xl border border-slate-200 bg-slate-50 px-8 py-6 sm:flex-row">
                <div class="text-center sm:text-left">
                    <p class="text-sm font-bold text-slate-900">Visit us in person</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $address }}</p>
                </div>
                <a href="{{ route('contact.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                    <i class="fas fa-map-location-dot"></i> Contact &amp; Map
                </a>
            </div>
        @endif
    </div>
</section>
@endsection
