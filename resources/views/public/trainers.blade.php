@extends('layouts.public')

@section('title', 'My Fitness Gym | Trainers')
@section('meta_description', 'Meet the trainers at My Fitness Gym and connect with the coaching team.')

@section('content')
    <section class="gallery-hero">
        <div class="shell gallery-hero-grid">
            <div data-reveal="up">
                <p class="eyebrow">Coaching team</p>
                <h1>Meet the trainers behind My Fitness Gym.</h1>
                <p class="hero-lead">Each trainer brings a personal coaching approach, practical gym knowledge, and direct contact for members who want support.</p>
                <div class="hero-actions">
                    <a href="{{ route('home') }}" class="secondary-button">Back Home</a>
                    <a href="{{ route('gallery') }}" class="primary-button">View Gallery</a>
                </div>
            </div>
            <div class="gallery-facts" data-reveal="scale">
                <div class="fact-chip"><strong>{{ $trainers->count() }}</strong><span>Active trainers</span></div>
                <div class="fact-chip"><strong>1:1</strong><span>Coach support</span></div>
                <div class="fact-chip"><strong>Direct</strong><span>Contact access</span></div>
            </div>
        </div>
    </section>

    <section class="section-shell">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Trainer profiles</p>
                    <h2>Professional coaching with clear contact details.</h2>
                </div>
                <p>Browse trainer profiles, read their focus areas, and reach out directly by phone or email.</p>
            </div>

            <div class="trainer-grid trainer-grid-page">
                @forelse ($trainers as $trainer)
                    @include('public.partials.trainer-card', ['trainer' => $trainer])
                @empty
                    <article class="trainer-card trainer-card-empty" data-reveal="up">
                        <div class="trainer-card-top">
                            <div class="trainer-crest">MG</div>
                            <div>
                                <p class="trainer-label">Trainer</p>
                                <h3>Profiles coming soon</h3>
                            </div>
                        </div>
                        <p class="trainer-bio">Trainer profiles are not yet published for public viewing.</p>
                    </article>
                @endforelse
            </div>
        </div>
    </section>
@endsection
