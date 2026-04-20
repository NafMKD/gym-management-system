@php
    $trainerProfile = $trainer->trainerProfile;
    $specializations = collect(preg_split('/[\r\n,]+/', (string) ($trainerProfile?->specializations ?? '')))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();
    $qualifications = collect(preg_split('/[\r\n,]+/', (string) ($trainerProfile?->qualifications ?? '')))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();
    $initials = collect(array_filter(explode(' ', $trainer->getName())))
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<article class="trainer-card" data-reveal="up">
    <div class="trainer-card-top">
        <div class="trainer-crest">{{ $initials ?: 'MG' }}</div>
        <div>
            <p class="trainer-label">Trainer</p>
            <h3>{{ $trainer->getName() }}</h3>
            <p class="trainer-contact-line">{{ $trainer->phone }}</p>
        </div>
    </div>

    <p class="trainer-bio">
        {{ $trainerProfile?->bio ?: __('A dedicated coach at My Fitness Gym focused on safe, consistent progress and better daily training habits.') }}
    </p>

    @if ($specializations->isNotEmpty())
        <div class="trainer-chip-list">
            @foreach ($specializations->take(4) as $specialization)
                <span class="trainer-chip">{{ $specialization }}</span>
            @endforeach
        </div>
    @endif

    @if ($qualifications->isNotEmpty())
        <p class="trainer-note">{{ $qualifications->take(2)->implode(' • ') }}</p>
    @endif

    <div class="trainer-actions">
        <a href="tel:{{ $trainer->phone }}" class="secondary-button">Call</a>
        @if (!empty($trainer->email))
            <a href="mailto:{{ $trainer->email }}" class="primary-button">Email</a>
        @else
            <span class="trainer-contact-line">{{ __('Available at reception') }}</span>
        @endif
    </div>
</article>
