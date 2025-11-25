<?php
/**
 * Feast Day Detail Template
 * Displays detailed information about a specific feast day
 */

$feast_info = $feast_descriptions[$feast_slug];

// Find the feast date
$feast_date = '';
$feast_month = '';
$feast_rank = '';
$feast_color = '';

foreach ($months as $month_num => $month_data) {
    if (!empty($month_data['days'])) {
        foreach ($month_data['days'] as $day_num => $day_data) {
            if (sanitize_title($day_data['name']) === $feast_slug) {
                $feast_date = $day_num;
                $feast_month = $month_data['name'];
                $feast_rank = $day_data['rank'];
                $feast_color = $day_data['color'];
                break 2;
            }
        }
    }
}
?>

<style>
.feast-detail-page {
    background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 60vh;
}

.feast-detail-header {
    background: linear-gradient(135deg, #1e4620 0%, #2d5016 50%, #3a6b1f 100%);
    color: white;
    padding: 3rem 0 2rem;
    position: relative;
    overflow: hidden;
}

.feast-detail-header:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M54.627 0l.83.828-1.415 1.415L51.8 0h2.827z' fill='%23fff' fill-opacity='0.05'/%3E%3C/svg%3E");
    opacity: 0.3;
}

.feast-detail-header .container {
    position: relative;
    z-index: 1;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    opacity: 0.9;
}

.breadcrumb a {
    color: white;
    text-decoration: none;
    transition: opacity 0.2s;
}

.breadcrumb a:hover {
    opacity: 0.8;
    text-decoration: underline;
}

.breadcrumb-separator {
    opacity: 0.6;
}

.feast-detail-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 1rem;
    line-height: 1.2;
}

.feast-detail-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.feast-date-display {
    font-size: 1.25rem;
    font-weight: 600;
    background: rgba(255,255,255,0.15);
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.feast-detail-content {
    padding: 3rem 0;
}

.feast-content-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.feast-content-card h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e4620;
    margin: 0 0 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid #3a6b1f;
}

.feast-content-card p {
    line-height: 1.8;
    color: #333;
    margin-bottom: 1rem;
}

.feast-content-card p:last-child {
    margin-bottom: 0;
}

.related-sites-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
}

.related-site-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #1e4620;
    text-decoration: none;
    padding: 0.75rem 1rem;
    background: #f5f5f5;
    border-radius: 8px;
    transition: all 0.2s;
    border-left: 3px solid #3a6b1f;
}

.related-site-link:hover {
    background: #e8f5e9;
    transform: translateX(4px);
}

.back-to-calendar {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: #1e4620;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s;
    margin-top: 2rem;
}

.back-to-calendar:hover {
    background: #2d5016;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 70, 32, 0.3);
}

@media (max-width: 768px) {
    .feast-detail-title {
        font-size: 2rem;
    }

    .feast-content-card {
        padding: 1.5rem;
    }
}
</style>

<main class="feast-detail-page">
    <section class="feast-detail-header">
        <div class="container">
            <nav class="breadcrumb">
                <a href="<?php echo home_url('/calendar/?year=' . $year); ?>">← Calendar <?php echo $year; ?></a>
            </nav>

            <h1 class="feast-detail-title"><?php echo esc_html($feast_info['full_name']); ?></h1>

            <div class="feast-detail-meta">
                <?php if ($feast_date && $feast_month): ?>
                    <span class="feast-date-display"><?php echo $feast_month; ?> <?php echo $feast_date; ?></span>
                <?php endif; ?>

                <?php if ($feast_rank): ?>
                    <span class="rank-badge <?php echo $feast_rank; ?>">
                        <?php echo ucfirst($feast_rank); ?>
                    </span>
                <?php endif; ?>

                <?php if ($feast_color): ?>
                    <span class="color-indicator color-<?php echo $feast_color; ?>" style="width: 28px; height: 28px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.5); box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="feast-detail-content">
        <div class="container">
            <?php if (!empty($feast_info['description'])): ?>
                <div class="feast-content-card">
                    <h2>About This Feast</h2>
                    <p><?php echo esc_html($feast_info['description']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($feast_info['significance'])): ?>
                <div class="feast-content-card">
                    <h2>Significance</h2>
                    <p><?php echo esc_html($feast_info['significance']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($feast_info['tradition'])): ?>
                <div class="feast-content-card">
                    <h2>Traditions & Customs</h2>
                    <p><?php echo esc_html($feast_info['tradition']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($feast_info['related_sites'])): ?>
                <div class="feast-content-card">
                    <h2>Related Sacred Sites</h2>
                    <p>Visit these Irish sacred sites associated with this feast:</p>
                    <div class="related-sites-list">
                        <?php
                        $sites = explode(', ', $feast_info['related_sites']);
                        foreach ($sites as $site):
                        ?>
                            <a href="<?php echo home_url('/monastic-sites/'); ?>" class="related-site-link">
                                <span>📍</span>
                                <span><?php echo esc_html($site); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <a href="<?php echo home_url('/calendar/?year=' . $year); ?>" class="back-to-calendar">
                ← Back to Calendar
            </a>
        </div>
    </section>
</main>
