@extends('layouts.app')

@section('title', 'My Applications')

@section('content')
@php
    $lookupApplicants = collect($lookupResult['applicants'] ?? []);
    $lookupFound = (bool) ($lookupResult['found'] ?? false);
    $hasLookup = filled($lookupFilters['phone'] ?? null) && filled($lookupFilters['dob'] ?? null);
@endphp

<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Admission Portal</p>
                <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">My Applications</h1>
            </div>
            <a href="{{ route('apply.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left"></i> Back to Apply
            </a>
        </div>

        {{-- Search box --}}
        <div class="mt-8 rounded-4xl bg-white p-6 shadow-sm ring-1 ring-slate-100 sm:p-8">
            <h2 class="text-base font-black text-slate-950">Already Applied? Search Your Application</h2>
            
            <form action="{{ route('apply.applications') }}" method="GET" class="mt-6 grid gap-5 sm:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label for="lookup-phone" class="form-label">Phone Number</label>
                    <input
                        id="lookup-phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone', $lookupFilters['phone'] ?? '') }}"
                        placeholder="017xxxxxxxx"
                        pattern="[0-9]{11}"
                        class="form-control"
                    >
                </div>
                <div>
                    <label for="lookup-dob" class="form-label">Date of Birth</label>
                    <input
                        id="lookup-dob"
                        name="dob"
                        type="date"
                        value="{{ old('dob', $lookupFilters['dob'] ?? '') }}"
                        class="form-control"
                    >
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>

            @if($hasLookup && ! $lookupFound)
                <div class="mt-6 rounded-2xl px-4 py-3 text-sm font-medium {{ $lookupFound ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200' : 'bg-sky-50 text-sky-900 ring-1 ring-sky-200' }}">
                    No matching applications were found.
                </div>

                @if($lookupError)
                    <div class="mt-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900 ring-1 ring-rose-200">{{ $lookupError }}</div>
                @endif
            @endif

            @if($lookupApplicants->isNotEmpty())
                <div class="mt-6">
                    @foreach($lookupApplicants as $application)
                        <div class="flex flex-col rounded-3xl border border-slate-100 bg-slate-50 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-950">App #{{ $application['application_no'] ?? 'N/A' }}</p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-wider text-indigo-600">
                                        {{ $application['application_status'] ?? 'pending' }}
                                    </p>
                                </div>
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                    <i class="fas fa-file-lines"></i>
                                </div>
                            </div>
                            <div class="mt-4 space-y-1 text-sm text-slate-500">
                                <p><i class="fas fa-phone w-4 text-slate-400"></i> {{ $application['phone'] ?? 'N/A' }}</p>
                                <p><i class="fas fa-cake-candles w-4 text-slate-400"></i> {{ $application['dob'] ?? 'N/A' }}</p>
                            </div>
                            @if($application['application_no'] ?? null)
                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <a
                                        href="{{ route('apply.slip', array_filter([
                                            'application_no' => $application['application_no'],
                                            'phone' => $lookupFilters['phone'] ?? null,
                                            'dob' => $lookupFilters['dob'] ?? null,
                                        ])) }}"
                                        class="btn btn-outline w-full justify-center"
                                    >
                                        <i class="fas fa-file-pdf"></i> Download Slip (PDF)
                                    </a>
                                    <a
                                        href="{{ route('apply.new', array_filter([
                                            'application_no' => $application['application_no'],
                                            'applicant_id' => $application['id'] ?? null,
                                            'phone' => $lookupFilters['phone'] ?? null,
                                            'dob' => $lookupFilters['dob'] ?? null,
                                        ])) }}"
                                        class="btn btn-primary w-full justify-center"
                                    >
                                        <i class="fas fa-pen-to-square"></i> Edit
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif($hasLookup && ! $lookupFound)
                <div class="mt-6 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500 ring-1 ring-slate-100">
                    No past application entries were returned for this lookup.
                </div>
            @endif

            {{-- Quick links --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-6">
                <p class="text-sm text-slate-500">Haven't applied yet?</p>
                <a href="{{ route('apply.new') }}" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-500">
                    <i class="fas fa-file-circle-plus"></i> Apply New
                </a>
            </div>
        </div>
    </div>
</section>

@push('styles')
    <style>
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 12px 16px;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #0f172a;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-primary {
            background: #0f172a;
            color: white;
        }

        .btn-primary:hover {
            background: #1e293b;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .btn-outline:hover {
            background: #f1f5f9;
        }
    </style>
@endpush
@endsection
