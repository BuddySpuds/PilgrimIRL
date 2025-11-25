<?php
/**
 * Template Name: Community Forum
 *
 * Template for the Forum page
 */

get_header();
?>

<style>
/* Forum Page - Custom Hero Background */
.forum-page .page-header {
    background-image: linear-gradient(rgba(45, 80, 22, 0.75), rgba(26, 48, 9, 0.85)),
                      url('/wp-content/uploads/2025/11/CelticCross.jpg') !important;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    padding: 8rem 0 !important;
    min-height: 400px !important;
}

/* Title and description text visibility */
.forum-page .page-title {
    color: white !important;
}

.forum-page .page-description {
    color: white !important;
}

/* Mobile optimization */
@media (max-width: 768px) {
    .forum-page .page-header {
        background-attachment: scroll !important;
        background-position: center center !important;
        padding: 5rem 0 !important;
        min-height: 300px !important;
    }
}
</style>

<main id="main" class="site-main forum-page">

    <!-- Forum Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Community Forum</h1>
            <p class="page-description">Connect with fellow pilgrims, share your experiences, and discuss Ireland's sacred heritage</p>
        </div>
    </section>

    <!-- Forum Content -->
    <section class="page-content">
        <div class="container">

            <div class="forum-intro">
                <h2>Join Our Community</h2>
                <p>Welcome to the PilgrimIRL community! This is a space for pilgrims, history enthusiasts, and spiritual seekers to connect, share experiences, and learn about Ireland's rich Christian heritage.</p>
            </div>

            <!-- Forum Coming Soon -->
            <div class="forum-status">
                <div class="status-card">
                    <span class="status-icon">🏗️</span>
                    <h3>Coming Soon</h3>
                    <p>Our community forum is currently under development. We're building a beautiful space for meaningful conversations about Ireland's sacred sites.</p>
                    <p><strong>Expected Launch:</strong> Early 2025</p>
                </div>
            </div>

            <!-- What You Can Do -->
            <div class="forum-features">
                <h2>What You'll Be Able To Do</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <span class="feature-icon">💬</span>
                        <h3>Share Your Journey</h3>
                        <p>Tell your pilgrimage stories and inspire others with your experiences visiting Ireland's sacred sites.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">❓</span>
                        <h3>Ask Questions</h3>
                        <p>Get answers about visiting sites, historical details, and practical travel information.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">🤝</span>
                        <h3>Connect with Pilgrims</h3>
                        <p>Meet like-minded people who share your interest in Ireland's Christian heritage.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">📅</span>
                        <h3>Plan Group Visits</h3>
                        <p>Organize pilgrimages and coordinate visits with other community members.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">📚</span>
                        <h3>Discuss History</h3>
                        <p>Dive deep into Irish Christian history, saints, and monastic traditions.</p>
                    </div>
                    <div class="feature-card">
                        <span class="feature-icon">🙏</span>
                        <h3>Spiritual Reflection</h3>
                        <p>Share prayers, reflections, and spiritual insights from your pilgrimages.</p>
                    </div>
                </div>
            </div>

            <!-- Get Notified -->
            <div class="forum-cta">
                <h2>Stay Updated</h2>
                <p>Want to be notified when the forum launches? Follow our blog for updates!</p>
                <a href="<?php echo home_url('/blog/'); ?>" class="btn-primary">Read Our Blog</a>
            </div>

        </div>
    </section>

</main>

<style>
.forum-page {
    background: var(--gray-50);
}

.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    color: var(--white);
    padding: 4rem 0;
    text-align: center;
}

.page-title {
    font-family: var(--font-heading);
    font-size: var(--font-5xl);
    margin: 0 0 1rem;
}

.page-description {
    font-size: var(--font-xl);
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto;
}

.page-content {
    padding: 4rem 0;
}

.forum-intro {
    max-width: 800px;
    margin: 0 auto 4rem;
    text-align: center;
}

.forum-intro h2 {
    font-family: var(--font-heading);
    font-size: var(--font-3xl);
    margin-bottom: 1rem;
    color: var(--gray-900);
}

.forum-status {
    max-width: 600px;
    margin: 0 auto 4rem;
}

.status-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 3rem;
    text-align: center;
    box-shadow: var(--shadow-lg);
    border: 2px dashed var(--primary-light);
}

.status-icon {
    font-size: 4rem;
    display: block;
    margin-bottom: 1rem;
}

.status-card h3 {
    font-family: var(--font-heading);
    font-size: var(--font-2xl);
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.forum-features h2 {
    font-family: var(--font-heading);
    font-size: var(--font-3xl);
    text-align: center;
    margin-bottom: 3rem;
    color: var(--gray-900);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 4rem;
}

.feature-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    transition: all 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.feature-icon {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 1rem;
}

.feature-card h3 {
    font-family: var(--font-heading);
    font-size: var(--font-xl);
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.feature-card p {
    color: var(--gray-700);
    line-height: 1.6;
    margin: 0;
}

.forum-cta {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 3rem;
    box-shadow: var(--shadow-lg);
}

.forum-cta h2 {
    font-family: var(--font-heading);
    font-size: var(--font-2xl);
    color: var(--gray-900);
    margin-bottom: 1rem;
}

.forum-cta p {
    font-size: var(--font-lg);
    color: var(--gray-700);
    margin-bottom: 2rem;
}

.btn-primary {
    display: inline-block;
    background: var(--primary-color);
    color: var(--white);
    padding: 1rem 2rem;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: var(--font-lg);
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

@media (max-width: 768px) {
    .page-header {
        padding: 3rem 0;
    }

    .page-title {
        font-size: var(--font-4xl);
    }

    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>
