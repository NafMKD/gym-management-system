@extends('layouts.portal')

@section('title')
    @include('layouts.header', ['title' => __('Member') . ' | ' . __('Classes')])
@endsection

@section('content')
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('Group classes') }}</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('member.home') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back to home') }}</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(!$membership)
                    <p class="text-muted">{{ __('You need an active membership to book classes.') }}</p>
                @else
                    <p class="text-muted small mb-3">{{ __('Book a spot in an upcoming session. Cancellations are allowed until the session ends.') }}</p>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Class') }}</th>
                                <th>{{ __('Trainer') }}</th>
                                <th>{{ __('Starts') }}</th>
                                <th>{{ __('Ends') }}</th>
                                <th>{{ __('Spots') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $s)
                                @php
                                    $cap = $s->effectiveCapacity();
                                    $used = (int) $s->bookings_active_count;
                                    $myBooking = $memberBookings[$s->id] ?? null;
                                    $booked = $myBooking !== null;
                                @endphp
                                <tr>
                                    <td>{{ $s->gymClass?->name }}</td>
                                    <td>{{ $s->trainer?->getName() }}</td>
                                    <td>{{ $s->starts_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $s->ends_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $used }} / {{ $cap }}</td>
                                    <td>
                                        @if($membership && !$booked && $used < $cap)
                                            <form method="POST" action="{{ route('member.classes.book') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="class_schedule_id" value="{{ $s->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Book') }}</button>
                                            </form>
                                        @elseif($membership && $booked && $myBooking && $myBooking->status !== 'cancelled')
                                            <form method="POST" action="{{ route('member.classes.cancel', $myBooking) }}" class="d-inline" onsubmit="return confirm(@json(__('Cancel booking?')))">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Cancel') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">{{ __('No upcoming sessions.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $schedules->links() }}
            </div>
        </section>
    </div>
@endsection
