@extends('layouts.app')

@section('title', 'Our Incharges')

@section('content')
<section class="py-16">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-slate-500">Leadership Team</p>
            <h1 class="mt-4 text-4xl font-black text-slate-950">Our Incharges</h1>

            <div class="mt-12 space-y-8">
                @forelse($incharges as $incharge)
                <div class="group flex flex-col md:flex-row overflow-hidden rounded-2xl bg-white border border-slate-100 transition-all duration-300 hover:shadow-xl">
                    <a href="{{ route('staff.show', $incharge->id) }}" class="block w-full md:w-72 shrink-0 aspect-square md:aspect-auto overflow-hidden bg-slate-200">
                        @if($incharge->photo)
                            <img src="{{ $incharge->photo }}"
                                alt="{{ $incharge->name }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @else
                            <div class="w-full h-full min-h-72 flex items-center justify-center bg-slate-200">
                                <i class="fas fa-user text-slate-400 text-6xl"></i>
                            </div>
                        @endif
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-2xl font-black text-slate-900 group-hover:text-accent transition-colors">
                                    <a href="{{ route('staff.show', $incharge->id) }}">{{ $incharge->name }}</a>
                                </h3>
                                <p class="text-sm font-bold text-accent uppercase tracking-wider mt-1">{{ $incharge->designation }}</p>
                                @if($incharge->department)
                                    <p class="text-xs font-semibold text-slate-500 mt-1">{{ $incharge->department }}</p>
                                @endif
                            </div>
                            @if($incharge->staff_code)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    ID: {{ $incharge->staff_code }}
                                </span>
                            @endif
                        </div>

                        <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                            @if($incharge->email)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Email</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700 break-all">{{ $incharge->email }}</dd>
                                </div>
                            @endif
                            @if($incharge->phone)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Phone</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->phone }}</dd>
                                </div>
                            @endif
                            @if($incharge->gender)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Gender</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700 capitalize">{{ $incharge->gender }}</dd>
                                </div>
                            @endif
                            @if($incharge->date_of_birth)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Date of Birth</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->date_of_birth->format('M d, Y') }}</dd>
                                </div>
                            @endif
                            @if($incharge->religion)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Religion</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->religion }}</dd>
                                </div>
                            @endif
                            @if($incharge->blood_group)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Blood Group</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->blood_group }}</dd>
                                </div>
                            @endif
                            @if($incharge->marital_status)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Marital Status</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->marital_status }}</dd>
                                </div>
                            @endif
                            @if($incharge->national_id)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">National ID</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->national_id }}</dd>
                                </div>
                            @endif
                            @if($incharge->joining_date)
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Joining Date</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->joining_date->format('M d, Y') }}</dd>
                                </div>
                            @endif
                            @if($incharge->present_address)
                                <div class="sm:col-span-2">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Present Address</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->present_address }}</dd>
                                </div>
                            @endif
                            @if($incharge->permanent_address)
                                <div class="sm:col-span-2">
                                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Permanent Address</dt>
                                    <dd class="mt-1 text-sm font-semibold text-slate-700">{{ $incharge->permanent_address }}</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <a href="{{ route('staff.show', $incharge->id) }}"
                               class="inline-flex items-center gap-2 text-sm font-bold text-accent hover:text-accent/80 transition-colors">
                                View full profile
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-20 text-center bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                    <i class="fas fa-user-tie text-slate-200 text-6xl mb-6"></i>
                    <p class="text-slate-400 font-bold">No incharges recorded yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
