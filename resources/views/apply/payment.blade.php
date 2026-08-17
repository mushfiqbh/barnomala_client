@extends('layouts.app')

@section('title', 'Continue Payment')

@section('content')
@php
    $instituteName = $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $phone = $options['institute.contact.phone'] ?? ($options['phone'] ?? '');
    $phone2 = $options['institute.contact.phone_extra'] ?? '';
    $email = $options['institute.contact.email'] ?? ($options['email'] ?? '');
    $whatsapp = $options['institute.social.whatsapp'] ?? ($options['institute.contact.phone'] ?? '');
    if (str_starts_with($whatsapp, '01')) {
        $whatsapp = '+88' . $whatsapp;
    }
    $waDigits = preg_replace('/[^0-9]/', '', $whatsapp);
@endphp

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Admission Portal</p>
                <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">Continue Payment</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-500">
                    Complete your admission fee payment or continue a pending transaction for {{ $instituteName }}.
                </p>
            </div>
            <a href="{{ route('apply.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left"></i> Back to Apply
            </a>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            {{-- Payment methods --}}
            <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <h2 class="text-lg font-black text-slate-950">How to pay your admission fee</h2>

                <ol class="mt-6 space-y-6">
                    <li class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-black text-white">1</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Find your application</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Go to
                                <a href="{{ route('apply.applications') }}" class="font-bold text-indigo-600 underline decoration-indigo-200 underline-offset-2 hover:decoration-indigo-400">My Applications</a>
                                and note down your Application No. or keep your submission confirmation handy.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-black text-white">2</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Send the fee via mobile banking</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Use bKash, Rocket or Nagad to send the admission fee to the institute's official number, using your Application No. as the reference.
                            </p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-black text-white">3</div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Confirm your payment</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Contact the admission office with your Transaction ID so the payment can be verified and your application marked as paid.
                            </p>
                        </div>
                    </li>
                </ol>

                @if($phone || $email)
                    <div class="mt-8 rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-100">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Payment verification</p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @if($phone)
                                <a href="tel:{{ $phone }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800">
                                    <i class="fas fa-phone"></i> {{ $phone }}
                                </a>
                            @endif
                            @if($waDigits)
                                <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
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

            {{-- Status / help panel --}}
            <div class="space-y-6">
                <div class="rounded-4xl bg-[#002147] p-8 text-white shadow-2xl">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-amber-300">
                        <i class="fas fa-circle-question text-xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-black">Already paid?</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-300">
                        If you have already made the payment, your application status will be updated by the admission
                        office. You can check the latest status in
                        <a href="{{ route('apply.applications') }}" class="font-bold text-amber-300 underline decoration-amber-400/40 underline-offset-2 hover:decoration-amber-300">My Applications</a>.
                    </p>
                    <a href="{{ route('apply.applications') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-5 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-300">
                        <i class="fas fa-folder-open"></i> Check Status
                    </a>
                </div>

                <div class="rounded-4xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                        <i class="fas fa-headset text-xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-black text-slate-950">Need help with payment?</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">
                        Facing an issue with your transaction? Our support team is ready to assist you with the payment process.
                    </p>
                    <a href="{{ route('apply.support') }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 transition hover:bg-slate-100">
                        <i class="fas fa-arrow-right"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
