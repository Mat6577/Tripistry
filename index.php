<?php
session_start();
include 'Components/header.php';
?>
<head>
    <link rel="stylesheet" href="/Tripistry-main/css/index.css">

</head>


    <!-- HERO -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-tag">✈ Travel Reimagined</div>
            <h1>Your Next Adventure<br>Starts Here</h1>
            <p>Compare tailored holiday packages from premium agencies or curate your own group expeditions — all in one place.</p>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="login.php" class="btn">Sign In</a>
                    <a href="register.php" class="btn btn-secondary">Create Account</a>
                </div>
            <?php else: ?>
                <div class="logged-in-msg">
                    👋 Welcome back! Head to your dashboard to explore packages or manage your bookings.
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-visual">
            <div class="globe-ring"></div>
            <div class="globe-ring ring-2"></div>
            <div class="globe-ring ring-3"></div>
            <div class="hero-icon">🌍</div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number">500+</span>
                <span class="stat-label">Packages Available</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">80+</span>
                <span class="stat-label">Destinations</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">120+</span>
                <span class="stat-label">Agency Partners</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">4.8★</span>
                <span class="stat-label">Average Rating</span>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-section">
        <h2>How Tripistry Works</h2>
        <p class="how-sub">Three simple steps to your dream holiday</p>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">01</div>
                <div class="step-icon">🔍</div>
                <h3>Browse Packages</h3>
                <p>Explore curated packages from verified travel agencies across dozens of destinations worldwide.</p>
            </div>
            <div class="step-card">
                <div class="step-num">02</div>
                <div class="step-icon">📋</div>
                <h3>Compare & Choose</h3>
                <p>Review itineraries, ratings, and pricing. Read real traveller reviews before you commit.</p>
            </div>
            <div class="step-card">
                <div class="step-num">03</div>
                <div class="step-icon">🎒</div>
                <h3>Book & Go</h3>
                <p>Confirm your booking instantly. Track everything from your personal bookings dashboard.</p>
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section">
        <h2>Why Choose Tripistry</h2>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🏆</span>
                <h3>Verified Agencies</h3>
                <p>Every agency on our platform is vetted and reviewed by real travellers.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3>Best Value</h3>
                <p>Compare packages side by side and find the best deal for your budget.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📍</span>
                <h3>AI-Powered Insights</h3>
                <p>Get smart destination insights to help you plan the perfect trip.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🔒</span>
                <h3>Secure Booking</h3>
                <p>Your bookings and data are protected with enterprise-grade security.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">⭐</span>
                <h3>Real Reviews</h3>
                <p>Honest ratings from verified travellers who've completed the trip.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">📱</span>
                <h3>Easy Management</h3>
                <p>Manage all your bookings from one clean, intuitive dashboard.</p>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
<?php if (!isset($_SESSION['user_id'])): ?>
    <section class="cta-section">
        <h2>Ready to Start Exploring?</h2>
        <p>Join thousands of travellers who plan smarter with Tripistry.</p>
        <div class="cta-buttons">
            <a href="register.php" class="btn btn-white">Get Started Free</a>
            <a href="login.php" class="btn btn-outline">Sign In</a>
        </div>
    </section>
<?php endif; ?>

<?php include 'Components/footer.php'; ?>