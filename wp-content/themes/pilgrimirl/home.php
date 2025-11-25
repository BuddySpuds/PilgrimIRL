<?php
/**
 * Template for displaying blog posts listing
 * This is used when a static page is set as the homepage
 */

get_header();
?>

<style>
/* Blog Listing Page - Custom Hero Background */
.blog-listing-header {
    background-image: linear-gradient(rgba(45, 80, 22, 0.75), rgba(26, 48, 9, 0.85)),
                      url('/wp-content/uploads/2025/11/Stamp.jpg') !important;
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    padding: 8rem 0 !important;
    min-height: 400px !important;
}

/* Title and subtitle text visibility */
.blog-listing-header .page-title {
    color: white !important;
}

.blog-listing-header .page-subtitle {
    color: white !important;
}

/* Mobile optimization */
@media (max-width: 768px) {
    .blog-listing-header {
        background-attachment: scroll !important;
        background-position: center center !important;
        padding: 5rem 0 !important;
        min-height: 300px !important;
    }
}
</style>

<main class="blog-listing-main">
    <!-- Blog Header -->
    <header class="blog-listing-header">
        <div class="container">
            <h1 class="page-title">PilgrimIRL Blog</h1>
            <p class="page-subtitle">Stories, insights, and discoveries from Ireland's sacred landscape</p>
        </div>
    </header>

    <!-- Blog Posts Grid -->
    <div class="blog-posts-section">
        <div class="container">
            <?php if (have_posts()) : ?>
                <div class="blog-posts-grid">
                    <?php
                    while (have_posts()) :
                        the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('blog-post-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-thumbnail-link">
                                    <?php the_post_thumbnail('large', array('class' => 'post-thumbnail')); ?>
                                </a>
                            <?php endif; ?>

                            <div class="post-card-content">
                                <div class="post-card-meta">
                                    <?php
                                    $categories = get_the_category();
                                    if ($categories) {
                                        echo '<span class="post-category">' . esc_html($categories[0]->name) . '</span>';
                                    }
                                    ?>
                                    <time datetime="<?php echo get_the_date('c'); ?>" class="post-date">
                                        <?php echo get_the_date('F j, Y'); ?>
                                    </time>
                                </div>

                                <h2 class="post-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <?php if (has_excerpt()) : ?>
                                    <div class="post-card-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="post-card-excerpt">
                                        <?php echo wp_trim_words(get_the_content(), 30, '...'); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="post-card-footer">
                                    <div class="post-author">
                                        <?php echo get_avatar(get_the_author_meta('ID'), 32); ?>
                                        <span class="author-name"><?php the_author(); ?></span>
                                    </div>
                                    <a href="<?php the_permalink(); ?>" class="read-more-link">
                                        Read More →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php
                    endwhile;
                    ?>
                </div>

                <!-- Pagination -->
                <div class="blog-pagination">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => '← Previous',
                        'next_text' => 'Next →',
                    ));
                    ?>
                </div>

            <?php else : ?>
                <div class="no-posts-found">
                    <div class="no-posts-content">
                        <h2>No posts found</h2>
                        <p>Check back soon for stories and insights about Ireland's sacred heritage!</p>
                        <a href="<?php echo home_url('/'); ?>" class="pilgrim-btn">Return Home</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();
