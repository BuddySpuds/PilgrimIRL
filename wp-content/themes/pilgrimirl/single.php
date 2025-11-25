<?php
/**
 * Template for displaying single blog posts
 */

get_header();
?>

<main class="blog-post-main">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('blog-post-article'); ?>>

            <!-- Hero Section -->
            <header class="blog-post-header">
                <div class="container">
                    <div class="blog-post-meta">
                        <?php
                        $categories = get_the_category();
                        if ($categories) {
                            echo '<span class="blog-category">' . esc_html($categories[0]->name) . '</span>';
                        }
                        ?>
                        <time datetime="<?php echo get_the_date('c'); ?>" class="blog-date">
                            <?php echo get_the_date('F j, Y'); ?>
                        </time>
                    </div>

                    <h1 class="blog-post-title"><?php the_title(); ?></h1>

                    <?php if (has_excerpt()) : ?>
                        <div class="blog-post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <div class="blog-author-info">
                        <?php
                        echo get_avatar(get_the_author_meta('ID'), 48);
                        ?>
                        <div class="blog-author-details">
                            <span class="author-name"><?php the_author(); ?></span>
                            <span class="reading-time">
                                <?php
                                $word_count = str_word_count(strip_tags(get_the_content()));
                                $reading_time = ceil($word_count / 200);
                                echo $reading_time . ' min read';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="blog-featured-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <!-- Content Section -->
            <div class="blog-post-content">
                <div class="container">
                    <div class="blog-content-wrapper">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <footer class="blog-post-footer">
                <div class="container">
                    <?php
                    $tags = get_the_tags();
                    if ($tags) :
                        ?>
                        <div class="blog-tags">
                            <span class="tags-label">Tags:</span>
                            <?php
                            foreach ($tags as $tag) {
                                echo '<a href="' . get_tag_link($tag->term_id) . '" class="tag-link">' . esc_html($tag->name) . '</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="blog-post-navigation">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        <?php if ($prev_post) : ?>
                            <a href="<?php echo get_permalink($prev_post); ?>" class="nav-link prev-post">
                                <span class="nav-label">← Previous Post</span>
                                <span class="nav-title"><?php echo esc_html($prev_post->post_title); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if ($next_post) : ?>
                            <a href="<?php echo get_permalink($next_post); ?>" class="nav-link next-post">
                                <span class="nav-label">Next Post →</span>
                                <span class="nav-title"><?php echo esc_html($next_post->post_title); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </footer>

        </article>
    <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
