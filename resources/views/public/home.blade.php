@extends('layouts.public')

@section('title', 'My Fitness Gym | Premium Training Club')
@section('meta_description', 'My Fitness Gym is a premium training club with modern strength spaces, guided coaching, membership packages, and a calm gym atmosphere.')

@section('content')
    @php($gymAddress = config('gym.address', []))
    @php($gymContact = config('gym.contact', []))

    <section class="hero-section">
        <div class="hero-noise"></div>
        <div class="shell hero-grid">
            <div class="hero-copy" data-reveal="up">
                <p class="eyebrow">Private performance club</p>
                <h1>Train with focus in a space built for consistency.</h1>
                <p class="hero-lead">
                    My Fitness Gym combines refined strength spaces, structured memberships, and supportive coaching for people who want a cleaner, more serious training routine.
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="primary-button">Enter Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="primary-button">Start Membership</a>
                    @endauth
                    <a href="{{ route('gallery') }}" class="secondary-button">View Gallery</a>
                </div>

                <div class="hero-contact-card" data-reveal="up">
                    <p class="eyebrow">Visit & contact</p>
                    <div class="hero-contact-grid">
                        <div class="hero-contact-item">
                            <span class="hero-contact-label"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Address</span>
                            <strong>{{ $gymAddress['area'] ?? 'Jimma, Merkato' }}</strong>
                            <p>{{ $gymAddress['building'] ?? 'Tsinat Building, 4th Floor' }}</p>
                        </div>
                        <div class="hero-contact-item">
                            <span class="hero-contact-label"><i class="fas fa-phone-alt" aria-hidden="true"></i> Contact</span>
                            <a href="tel:{{ $gymContact['phone_href'] ?? '+251917553839' }}">{{ $gymContact['phone_display'] ?? '(251) 917-55-3839' }}</a>
                            <a href="mailto:{{ $gymContact['email'] ?? 'myfitness743@gmail.com' }}">{{ $gymContact['email'] ?? 'myfitness743@gmail.com' }}</a>
                        </div>
                    </div>
                </div>

                <div class="hero-metrics">
                    <div class="metric-card" data-reveal="up">
                        <strong data-counter="12">12</strong>
                        <span>Coach pods</span>
                    </div>
                    <div class="metric-card" data-reveal="up">
                        <strong data-counter="5">5</strong>
                        <span>Recovery zones</span>
                    </div>
                    <div class="metric-card" data-reveal="up">
                        <strong data-counter="24">24</strong>
                        <span>Hour access</span>
                    </div>
                </div>
            </div>

            <div class="hero-visual" data-reveal="scale">
                <div class="visual-column tall">
                    <article class="visual-card glass-card photo-card">
                        <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=900&q=80" alt="Athlete training with battle ropes" loading="eager">
                        <div class="visual-overlay">
                            <span>Strength Floor</span>
                            <strong>Focused daily training</strong>
                        </div>
                    </article>
                    <article class="visual-card stat-slab">
                        <p class="slab-label">Refined floor</p>
                        <h3>92%</h3>
                        <p>Clean lighting, calm acoustics, and thoughtful spacing keep the floor focused.</p>
                    </article>
                </div>
                <div class="visual-column short">
                    <article class="visual-card stat-slab accent">
                        <p class="slab-label">Training zones</p>
                        <ul class="slab-list">
                            <li>Strength Lab</li>
                            <li>HIIT Theater</li>
                            <li>Boxing Bay</li>
                            <li>Recovery Lounge</li>
                        </ul>
                    </article>
                    <article class="visual-card glass-card photo-card">
                        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=900&q=80" alt="Modern gym interior with equipment" loading="lazy">
                        <div class="visual-overlay">
                            <span>Recovery Corner</span>
                            <strong>Strong sessions, clean finishes</strong>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="marquee-band">
        <div class="marquee-track">
            <span>Strength</span>
            <span>Coaching</span>
            <span>Recovery</span>
            <span>Mobility</span>
            <span>Boxing</span>
            <span>Conditioning</span>
            <span>Strength</span>
            <span>Coaching</span>
            <span>Recovery</span>
            <span>Mobility</span>
            <span>Boxing</span>
            <span>Conditioning</span>
        </div>
    </section>

    <section id="experience" class="section-shell experience-section">
        <div class="shell">
            <div class="section-intro" data-reveal="up">
                <p class="eyebrow">Why it feels different</p>
                <h2>Elegant design. Serious training.</h2>
                <p>The club feels premium without becoming loud, so the focus stays on movement, rhythm, attendance, and progress.</p>
            </div>

            <div class="feature-grid">
                <article class="feature-card" data-reveal="up">
                    <i class="fas fa-bolt"></i>
                    <h3>Calm Atmosphere</h3>
                    <p>Soft contrast, composed lighting, and a floor that feels focused from the first step.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <i class="fas fa-dumbbell"></i>
                    <h3>Curated Equipment</h3>
                    <p>Strength, conditioning, and cardio arranged with cleaner sightlines and easier flow.</p>
                </article>
                <article class="feature-card" data-reveal="up">
                    <i class="fas fa-heartbeat"></i>
                    <h3>Recovery Built In</h3>
                    <p>Mobility and cooldown spaces help every session finish as well as it starts.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="programs" class="section-shell">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Training paths</p>
                    <h2>Simple programs with a premium feel.</h2>
                </div>
                <p>Choose strength, conditioning, boxing, or recovery blocks and move at your own pace.</p>
            </div>

            <div class="program-grid">
                <article class="program-card photo-program" data-reveal="up">
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=900&q=80" alt="Athlete doing strength training" loading="lazy">
                    <div class="program-copy">
                        <span>01</span>
                        <h3>Strength Architecture</h3>
                        <p>Foundational lifts and clean progression.</p>
                    </div>
                </article>
                <article class="program-card" data-reveal="up">
                    <span>02</span>
                    <h3>Velocity Conditioning</h3>
                    <p>Fast circuits, strong pacing, and a clear athletic rhythm.</p>
                </article>
                <article class="program-card" data-reveal="up">
                    <span>03</span>
                    <h3>Boxing & Core</h3>
                    <p>Sharp rounds, focused footwork, and powerful core work.</p>
                </article>
                <article class="program-card" data-reveal="up">
                    <span>04</span>
                    <h3>Mobility Reset</h3>
                    <p>Restore range, soften fatigue, and leave the room feeling lighter.</p>
                </article>
            </div>
        </div>
    </section>

    <section id="packages" class="section-shell packages-section">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Membership packages</p>
                    <h2>Packages built around validity and gym check-ins.</h2>
                </div>
                <p>At My Fitness Gym, each membership package sets how long access stays valid and how many gym entries are included in that period.</p>
            </div>

            <div class="package-grid">
                @forelse ($packages as $package)
                    <article class="package-card" data-reveal="up">
                        <p class="package-label">Membership package</p>
                        <h3>{{ $package->name }}</h3>
                        <div class="package-meta">
                            <div>
                                <strong>{{ $package->duration }}</strong>
                                <span>Valid days</span>
                            </div>
                            <div>
                                <strong>{{ $package->granted_days }}</strong>
                                <span>Gym check-ins</span>
                            </div>
                        </div>
                        <p>{{ $package->description ?: __('A structured option for members who want a clear training rhythm and a simple attendance plan.') }}</p>
                    </article>
                @empty
                    <article class="package-card package-card-empty" data-reveal="up">
                        <p class="package-label">Membership package</p>
                        <h3>Packages coming soon</h3>
                        <p>The team is preparing membership options for public display. Contact reception for the current package list.</p>
                    </article>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section-shell spotlight-section">
        <div class="shell spotlight-grid">
            <div class="spotlight-copy" data-reveal="up">
                <p class="eyebrow">Membership workflow</p>
                <h2>Membership built around your routine.</h2>
                <p>Members train inside a clear system: choose a package, use the included check-ins within its validity period, and keep progress moving with consistent attendance.</p>
                <div class="spotlight-points">
                    <div>
                        <strong>Package selection</strong>
                        <span>Choose a membership based on validity period and included gym entries.</span>
                    </div>
                    <div>
                        <strong>Attendance tracking</strong>
                        <span>Each visit is recorded so members can stay on top of usage and routine.</span>
                    </div>
                    <div>
                        <strong>Flexible continuity</strong>
                        <span>Packages can be renewed, upgraded, and extended as training plans evolve.</span>
                    </div>
                </div>
            </div>

            <div class="spotlight-panel" data-reveal="scale">
                <div class="energy-ring"></div>
                <div class="panel-surface">
                    <div class="panel-topline">
                        <span>Member success cadence</span>
                        <strong>Week 01 to Week 12</strong>
                    </div>
                    <div class="progress-stack">
                        <div>
                            <label>Consistency</label>
                            <div class="progress-bar"><span style="width: 94%"></span></div>
                        </div>
                        <div>
                            <label>Strength gain</label>
                            <div class="progress-bar"><span style="width: 81%"></span></div>
                        </div>
                        <div>
                            <label>Mobility quality</label>
                            <div class="progress-bar"><span style="width: 76%"></span></div>
                        </div>
                    </div>
                    <div class="mini-quote">
                        <p>"Clean, calm, and motivating. It makes showing up easier."</p>
                        <span>Member feedback</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-shell trainers-preview-section">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Coaching team</p>
                    <h2>Meet the trainers behind the floor.</h2>
                </div>
                <a href="{{ route('trainers') }}" class="inline-link">Open trainer page</a>
            </div>

            <div class="trainer-grid">
                @forelse ($featuredTrainers as $trainer)
                    @include('public.partials.trainer-card', ['trainer' => $trainer])
                @empty
                    <article class="trainer-card trainer-card-empty" data-reveal="up">
                        <div class="trainer-card-top">
                            <div class="trainer-crest">MG</div>
                            <div>
                                <p class="trainer-label">Trainer</p>
                                <h3>Coaching team</h3>
                            </div>
                        </div>
                        <p class="trainer-bio">Trainer profiles will appear here once the coaching team is published for public viewing.</p>
                    </article>
                @endforelse
            </div>
        </div>
    </section>

    <section id="schedule" class="section-shell schedule-section">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Weekly rhythm</p>
                    <h2>A weekly rhythm that feels effortless.</h2>
                </div>
                <p>Train any time, or step into focused coached sessions across the day.</p>
            </div>

            <div class="schedule-grid">
                <article class="schedule-card" data-reveal="up">
                    <span>06:00</span>
                    <h3>Founders Lift</h3>
                    <p>Focused morning strength with coach support.</p>
                </article>
                <article class="schedule-card" data-reveal="up">
                    <span>12:30</span>
                    <h3>Express HIIT</h3>
                    <p>Quick lunchtime intensity for busy schedules.</p>
                </article>
                <article class="schedule-card" data-reveal="up">
                    <span>18:00</span>
                    <h3>Boxing Circuit</h3>
                    <p>Power rounds and sharp conditioning.</p>
                </article>
                <article class="schedule-card" data-reveal="up">
                    <span>20:00</span>
                    <h3>Mobility After Dark</h3>
                    <p>Stretch, release, and finish the day well.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section-shell gallery-preview-section">
        <div class="shell">
            <div class="split-head" data-reveal="up">
                <div>
                    <p class="eyebrow">Visual atmosphere</p>
                    <h2>A space with presence.</h2>
                </div>
                <a href="{{ route('gallery') }}" class="inline-link">View full gallery</a>
            </div>

            <div class="gallery-preview-grid">
                <figure class="gallery-shot wide" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?auto=format&fit=crop&w=1200&q=80" alt="Athlete in modern gym space" loading="lazy">
                </figure>
                <figure class="gallery-shot" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1599058917212-d750089bc07e?auto=format&fit=crop&w=900&q=80" alt="Fitness class with lights" loading="lazy">
                </figure>
                <figure class="gallery-shot" data-reveal="scale">
                    <img src="https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?auto=format&fit=crop&w=900&q=80" alt="Gym equipment and training floor" loading="lazy">
                </figure>
            </div>
        </div>
    </section>

    <section class="section-shell testimonials-section">
        <div class="shell">
            <div class="section-intro" data-reveal="up">
                <p class="eyebrow">Member voices</p>
                <h2>Members notice the mood as much as the results.</h2>
            </div>
            <div class="testimonial-grid">
                <blockquote class="testimonial-card" data-reveal="up">
                    <p>"It feels polished and focused, never crowded or noisy."</p>
                    <footer>Hana, creative director</footer>
                </blockquote>
                <blockquote class="testimonial-card" data-reveal="up">
                    <p>"The coaching gave me structure, and the space made it easy to stay consistent."</p>
                    <footer>Samuel, entrepreneur</footer>
                </blockquote>
                <blockquote class="testimonial-card" data-reveal="up">
                    <p>"Beautiful room, smart trainers, and recovery that actually helps."</p>
                    <footer>Rahel, architect</footer>
                </blockquote>
            </div>
        </div>
    </section>

    <section class="section-shell cta-section">
        <div class="shell cta-panel" data-reveal="scale">
            <div>
                <p class="eyebrow">Membership access</p>
                <h2>Step into a gym that feels polished from the first visit.</h2>
                <p>Beautiful space, clear coaching, and serious training without the visual noise.</p>
            </div>
            <div class="cta-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="primary-button">Open Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="primary-button">Login to Begin</a>
                @endauth
                <a href="{{ route('trainers') }}" class="secondary-button">Meet Trainers</a>
            </div>
        </div>
    </section>
@endsection
