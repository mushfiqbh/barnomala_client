@extends('layouts.app')

@section('title', 'Online Apply')

@section('content')
@php
    $instituteName = $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $phone = $options['institute.contact.phone'] ?? ($options['phone'] ?? '');
    $email = $options['institute.contact.email'] ?? ($options['email'] ?? '');
    $cards = [
        [
            'title' => 'Apply New',
            'desc' => 'Start a new admission application for the upcoming academic year.',
            'icon' => 'fa-file-circle-plus',
            'gradient' => 'from-indigo-600 to-blue-600',
            'url' => route('apply.new'),
            'cta' => 'Start New Application',
        ],
        [
            'title' => 'My Applications',
            'desc' => 'Search by phone number & date of birth to review or edit your submitted applications.',
            'icon' => 'fa-folder-open',
            'gradient' => 'from-emerald-600 to-teal-600',
            'url' => route('apply.applications'),
            'cta' => 'Find My Applications',
        ],
        [
            'title' => 'Continue Payment',
            'desc' => 'Complete your admission fee payment or continue a pending transaction.',
            'icon' => 'fa-credit-card',
            'gradient' => 'from-amber-500 to-orange-600',
            'url' => route('apply.payment'),
            'cta' => 'Continue Payment',
        ],
        [
            'title' => 'Support',
            'desc' => 'Need help with your application or payment? Contact the admission office.',
            'icon' => 'fa-headset',
            'gradient' => 'from-rose-600 to-pink-600',
            'url' => route('apply.support'),
            'cta' => 'Get Support',
        ],
    ];
@endphp

<section class="py-16">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8">
        {{-- 4 action cards --}}
        <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach($cards as $card)
                <a
                    href="{{ $card['url'] }}"
                    class="group card-hover relative flex flex-col rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100"
                >
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-linear-to-br {{ $card['gradient'] }} text-white shadow-lg">
                        <i class="fas {{ $card['icon'] }} text-xl"></i>
                    </div>

                    <h2 class="mt-6 text-xl font-black text-slate-950">{{ $card['title'] }}</h2>
                    <p class="mt-3 flex-1 text-sm leading-6 text-slate-500">{{ $card['desc'] }}</p>
                </a>
            @endforeach
        </div>

        {{-- How it works --}}
        <div class="mt-16 rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">How it works</p>
            
            <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-sm font-black text-white">1</div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Submit Application</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Fill in the online form with student &amp; guardian details and submit.</p>
                </div>
                <div class="relative">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-sm font-black text-white">2</div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Get Application No.</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">A unique application number is generated for every submission.</p>
                </div>
                <div class="relative">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-sm font-black text-white">3</div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Pay Admission Fee</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Complete the fee payment through your preferred mobile banking.</p>
                </div>
                <div class="relative">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-600 text-sm font-black text-white">4</div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">Track &amp; Get Support</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Review your application status anytime, or reach out for help.</p>
                </div>
            </div>
        </div>

        {{-- Help strip --}}
        @if($phone || $email)
            <div class="mt-10 flex flex-col items-center justify-between gap-4 rounded-4xl border border-slate-200 bg-slate-50 px-8 py-6 sm:flex-row">
                <div class="text-center sm:text-left">
                    <p class="text-sm font-bold text-slate-900">Need assistance with your application?</p>
                    <p class="mt-1 text-sm text-slate-500">Our admission office is happy to help you.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    @if($phone)
                        <a href="tel:{{ $phone }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                            <i class="fas fa-phone"></i> {{ $phone }}
                        </a>
                    @endif
                    @if($email)
                        <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 transition hover:bg-slate-100">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
