@extends('layouts.public')

@section('title', 'My Fitness Gym | Gallery')
@section('meta_description', 'Explore the spaces, energy, and training atmosphere behind My Fitness Gym.')

@section('content')
    <section class="gallery-hero">
        <div class="shell gallery-hero-grid">
            <div data-reveal="up">
                <p class="eyebrow">Gallery</p>
                <h1>A closer look at the space.</h1>
                <p class="hero-lead">A softer, more refined view of the club, its zones, and the training atmosphere.</p>
                <div class="hero-actions">
                    <a href="{{ route('home') }}" class="secondary-button">Back Home</a>
                    <a href="{{ route('trainers') }}" class="primary-button">Meet Trainers</a>
                </div>
            </div>
            <div class="gallery-facts" data-reveal="scale">
                <div class="fact-chip"><strong>18K</strong><span>Sq ft floor</span></div>
                <div class="fact-chip"><strong>4</strong><span>Signature studios</span></div>
                <div class="fact-chip"><strong>7</strong><span>Visual zones</span></div>
            </div>
        </div>
    </section>

    <section class="section-shell">
        <div class="shell">
            <div class="mosaic-grid">
                <figure class="mosaic-item tall" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=1200&q=80" alt="Athlete with dumbbells in dramatic lighting" loading="eager">
                </figure>
                <figure class="mosaic-item wide" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=1600&q=80" alt="Luxury gym interior with futuristic feel" loading="eager">
                </figure>
                <figure class="mosaic-item" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1580261450046-d0a30080dc9b?auto=format&fit=crop&w=900&q=80" alt="Training session on modern exercise machines" loading="lazy">
                </figure>
                <figure class="mosaic-item" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?auto=format&fit=crop&w=900&q=80" alt="Athlete doing cable work in premium gym" loading="lazy">
                </figure>
                <figure class="mosaic-item wide" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1574680178050-55c6a6a96e0a?auto=format&fit=crop&w=1600&q=80" alt="High energy group fitness class" loading="lazy">
                </figure>
                <figure class="mosaic-item tall" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?auto=format&fit=crop&w=1200&q=80" alt="Boxing and conditioning setup" loading="lazy">
                </figure>
            </div>
        </div>
    </section>

    <section class="section-shell">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Spaces that perform</p>
                    <h2>Every zone is designed to feel open, focused, and premium.</h2>
                </div>
                <p>From strength to recovery, the flow stays clean, comfortable, and easy on the eye.</p>
            </div>

            <div class="zone-grid">
                <article class="zone-card" data-reveal="up">
                    <h3>Strength Lab</h3>
                    <p>Heavy lifts and machine work in a cleaner, calmer layout.</p>
                </article>
                <article class="zone-card" data-reveal="up">
                    <h3>Cardio Runway</h3>
                    <p>Treadmills, bikes, and rowers with a softer visual rhythm.</p>
                </article>
                <article class="zone-card" data-reveal="up">
                    <h3>Combat Studio</h3>
                    <p>Boxing and conditioning in a focused, disciplined room.</p>
                </article>
                <article class="zone-card" data-reveal="up">
                    <h3>Recovery Lounge</h3>
                    <p>Mobility, breath work, and release to close the session well.</p>
                </article>
            </div>
        </div>
    </section>
@endsection
