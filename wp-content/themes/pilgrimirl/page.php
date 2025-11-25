<?php
/**
 * Standard Page Template
 *
 * Generic template for pages like About, Privacy Policy, Terms, etc.
 */

get_header();
?>

<main id="main" class="site-main standard-page">

    <?php while (have_posts()) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('standard-page-article'); ?>>

            <!-- Page Header -->
            <header class="page-entry-header">
                <div class="container">
                    <div class="page-header-content">
                        <div class="page-breadcrumbs">
                            <a href="<?php echo home_url(); ?>">Home</a> &raquo;
                            <span><?php the_title(); ?></span>
                        </div>

                        <h1 class="page-entry-title"><?php the_title(); ?></h1>

                        <?php if (has_excerpt()) : ?>
                            <div class="page-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-entry-content">
                <div class="container">
                    <div class="page-content-wrapper">

                        <?php
                        // Check if page has any content
                        $content = get_the_content();
                        if (empty($content) || trim(strip_tags($content)) == '') :
                            // Show placeholder for empty pages
                            ?>
                            <div class="empty-page-notice">
                                <div class="notice-icon">📝</div>
                                <h2>This page is under construction</h2>
                                <p>Content for this page is coming soon. Please check back later.</p>
                                <div class="page-actions">
                                    <a href="<?php echo home_url(); ?>" class="pilgrim-btn pilgrim-btn-primary">
                                        Return to Homepage
                                    </a>
                                </div>
                            </div>
                        <?php else : ?>
                            <!-- Display actual content -->
                            <div class="page-content">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            // Page navigation for multi-page content
                            wp_link_pages(array(
                                'before' => '<div class="page-links">' . __('Pages:', 'pilgrimirl'),
                                'after'  => '</div>',
                            ));
                            ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="page-comments">
                    <div class="container">
                        <?php comments_template(); ?>
                    </div>
                </div>
            <?php endif; ?>

        </article>

    <?php endwhile; ?>

</main>

<style>
/* Standard Page Styling */
.standard-page {
    background: var(--gray-50);
}

.page-entry-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: var(--white);
    padding: var(--space-16) 0 var(--space-12);
    margin-bottom: var(--space-12);
}

.page-header-content {
    max-width: 800px;
    margin: 0 auto;
}

.page-breadcrumbs {
    font-size: var(--font-sm);
    margin-bottom: var(--space-4);
    opacity: 0.9;
}

.page-breadcrumbs a {
    color: var(--white);
    text-decoration: none;
    transition: opacity 0.2s;
}

.page-breadcrumbs a:hover {
    opacity: 0.8;
}

.page-entry-title {
    font-size: var(--font-4xl);
    font-family: var(--font-heading);
    font-weight: 700;
    margin: 0 0 var(--space-4);
    line-height: 1.2;
}

.page-excerpt {
    font-size: var(--font-lg);
    line-height: 1.6;
    opacity: 0.95;
}

.page-entry-content {
    padding: var(--space-12) 0;
}

.page-content-wrapper {
    max-width: 800px;
    margin: 0 auto;
    background: var(--white);
    padding: var(--space-12);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.page-content {
    font-size: var(--font-base);
    line-height: 1.8;
    color: var(--gray-900);
}

.page-content h2 {
    font-family: var(--font-heading);
    font-size: var(--font-2xl);
    font-weight: 600;
    color: var(--primary-color);
    margin: var(--space-8) 0 var(--space-4);
}

.page-content h3 {
    font-family: var(--font-heading);
    font-size: var(--font-xl);
    font-weight: 600;
    color: var(--secondary-color);
    margin: var(--space-6) 0 var(--space-3);
}

.page-content p {
    margin-bottom: var(--space-4);
}

.page-content ul,
.page-content ol {
    margin: var(--space-4) 0;
    padding-left: var(--space-8);
}

.page-content li {
    margin-bottom: var(--space-2);
}

.page-content a {
    color: var(--primary-color);
    text-decoration: underline;
    transition: color 0.2s;
}

.page-content a:hover {
    color: var(--secondary-color);
}

.page-content blockquote {
    border-left: 4px solid var(--primary-color);
    padding-left: var(--space-6);
    margin: var(--space-6) 0;
    font-style: italic;
    color: var(--gray-700);
}

/* Empty Page Notice */
.empty-page-notice {
    text-align: center;
    padding: var(--space-16) var(--space-8);
}

.notice-icon {
    font-size: 4rem;
    margin-bottom: var(--space-6);
    opacity: 0.6;
}

.empty-page-notice h2 {
    font-family: var(--font-heading);
    font-size: var(--font-2xl);
    color: var(--gray-800);
    margin-bottom: var(--space-4);
}

.empty-page-notice p {
    font-size: var(--font-lg);
    color: var(--gray-600);
    margin-bottom: var(--space-8);
}

.page-actions {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
    flex-wrap: wrap;
}

/* Page Navigation */
.page-links {
    margin-top: var(--space-8);
    padding-top: var(--space-6);
    border-top: 1px solid var(--gray-200);
    font-weight: 600;
}

.page-links a {
    display: inline-block;
    padding: var(--space-2) var(--space-4);
    background: var(--gray-100);
    border-radius: var(--radius-md);
    margin: 0 var(--space-1);
    text-decoration: none;
    transition: background 0.2s;
}

.page-links a:hover {
    background: var(--primary-color);
    color: var(--white);
}

/* Comments Section */
.page-comments {
    background: var(--white);
    padding: var(--space-12) 0;
    margin-top: var(--space-12);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-entry-header {
        padding: var(--space-10) 0 var(--space-8);
    }

    .page-entry-title {
        font-size: var(--font-3xl);
    }

    .page-content-wrapper {
        padding: var(--space-8);
    }

    .page-entry-content {
        padding: var(--space-8) 0;
    }

    .empty-page-notice {
        padding: var(--space-12) var(--space-6);
    }
}
</style>

<?php get_footer(); ?>
