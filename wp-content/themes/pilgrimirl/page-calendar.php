<?php
/**
 * Template Name: Liturgical Calendar
 *
 * Enhanced template for displaying Irish Catholic Liturgical Calendar
 * with admin-managed events, search, and export functionality
 */

get_header();

// Get year and feast from URL parameters
$year_param = get_query_var('year') ? get_query_var('year') : (isset($_GET['year']) ? $_GET['year'] : '');
$year = $year_param ? intval($year_param) : date('Y');

$feast_param = get_query_var('feast') ? get_query_var('feast') : (isset($_GET['feast']) ? $_GET['feast'] : '');
$feast_slug = $feast_param ? sanitize_title($feast_param) : '';

// Only allow 2025 and 2026 for now
if (!in_array($year, array(2025, 2026))) {
    $year = 2025;
}

// Load calendar data from PHP file (use get_stylesheet_directory for child theme)
$calendar_file = get_stylesheet_directory() . '/includes/calendar-data-' . $year . '.php';
$calendar_data = file_exists($calendar_file) ? include($calendar_file) : null;

if (!$calendar_data) {
    echo '<div class="container" style="padding: 2rem;"><p>Calendar data not available for ' . esc_html($year) . '.</p></div>';
    get_footer();
    return;
}

$months = $calendar_data['months'];
$irish_saints = $calendar_data['irish_saints'];
$liturgical_colors = $calendar_data['liturgical_colors'];

// Feast day descriptions for detail pages
$feast_descriptions = array(
    'the-blessed-virgin-mary-the-mother-of-god' => array(
        'full_name' => 'The Blessed Virgin Mary, the Mother of God',
        'description' => 'This solemnity honors Mary as the Mother of God (Theotokos), celebrated on the octave of Christmas. It is one of the oldest Marian feasts in the Church.',
        'significance' => 'Celebrates Mary\'s divine motherhood and her role in salvation history.',
        'tradition' => 'Often observed with special prayers and devotions to Our Lady.',
    ),
    'the-epiphany-of-the-lord' => array(
        'full_name' => 'The Epiphany of the Lord',
        'description' => 'Celebrates the manifestation of Christ to the Gentiles, represented by the visit of the Magi to the infant Jesus.',
        'significance' => 'Marks the revelation of Jesus as the Savior of all nations, not just the Jewish people.',
        'tradition' => 'Traditional blessing of chalk, gold, frankincense, and myrrh. Homes are blessed with the inscription of the Magi\'s initials.',
    ),
    'saint-brigid-of-kildare' => array(
        'full_name' => 'Saint Brigid of Kildare',
        'description' => 'Brigid (c. 451-525 AD) is one of Ireland\'s three patron saints. She founded a monastery in Kildare and is known for her charity, miracles, and role in early Irish Christianity.',
        'significance' => 'Brigid represents the strong tradition of Irish monasticism and women\'s leadership in the early Church. She is celebrated as a protector of dairy farmers, newborns, and fugitives.',
        'tradition' => 'St. Brigid\'s crosses are made from rushes and hung in homes for protection. Her feast coincides with Imbolc, marking the beginning of spring.',
        'related_sites' => 'Kildare Abbey, St. Brigid\'s Cathedral in Kildare',
    ),
    'the-presentation-of-the-lord' => array(
        'full_name' => 'The Presentation of the Lord',
        'description' => 'Commemorates the presentation of Jesus at the Temple in Jerusalem, forty days after his birth, as required by Jewish law.',
        'significance' => 'Celebrates Christ as the Light of the World and marks the meeting between the Old and New Covenants through Simeon and Anna.',
        'tradition' => 'Candles are blessed on this day (Candlemas), symbolizing Christ as the Light of the World.',
    ),
    'saint-patrick-bishop-patron-of-ireland' => array(
        'full_name' => 'Saint Patrick, Bishop, Patron of Ireland',
        'description' => 'Patrick (c. 385-461 AD) is the primary patron saint of Ireland. Born in Roman Britain, he was captured by Irish raiders and enslaved in Ireland for six years. After escaping, he returned as a missionary bishop and converted much of Ireland to Christianity.',
        'significance' => 'Patrick is credited with bringing Christianity to Ireland, establishing monasteries, churches, and schools. His teachings blended Christian doctrine with Irish culture, creating a unique expression of faith.',
        'tradition' => 'Celebrated worldwide with parades, wearing green, shamrocks (which Patrick used to explain the Trinity), and attending Mass. Major pilgrimage to Croagh Patrick.',
        'related_sites' => 'Croagh Patrick, Downpatrick Cathedral, Hill of Tara, Hill of Slane, St. Patrick\'s Cathedral Dublin',
    ),
    'saint-joseph-husband-of-mary' => array(
        'full_name' => 'Saint Joseph, Husband of Mary',
        'description' => 'Joseph was the earthly father of Jesus and the spouse of the Blessed Virgin Mary. A righteous carpenter, he is the patron saint of workers, fathers, and the universal Church.',
        'significance' => 'Joseph is honored for his faithful obedience to God\'s will, his protection of the Holy Family, and his quiet strength.',
        'tradition' => 'Special devotions to St. Joseph, patron of a happy death and the universal Church.',
    ),
    'the-annunciation-of-the-lord' => array(
        'full_name' => 'The Annunciation of the Lord',
        'description' => 'Celebrates the angel Gabriel\'s announcement to Mary that she would conceive and bear Jesus, the Son of God.',
        'significance' => 'Marks the Incarnation - the moment God became man. Mary\'s "yes" to God changed salvation history.',
        'tradition' => 'Nine months before Christmas, this feast celebrates the beginning of our redemption.',
    ),
    'easter-sunday' => array(
        'full_name' => 'Easter Sunday',
        'description' => 'The greatest feast of the Christian calendar, celebrating the Resurrection of Jesus Christ from the dead on the third day after his crucifixion.',
        'significance' => 'Easter is the foundation of Christian faith. Through Christ\'s resurrection, death is conquered and eternal life is offered to all.',
        'tradition' => 'Vigil Mass with lighting of the Paschal candle, baptisms, renewal of baptismal promises, festive celebrations, and the cry "Alleluia!"',
    ),
    'saint-kevin-abbot' => array(
        'full_name' => 'Saint Kevin, Abbot',
        'description' => 'Kevin (c. 498-618 AD) was an Irish abbot and hermit saint who founded the monastery at Glendalough in County Wicklow. Known for his asceticism and love of nature.',
        'significance' => 'Kevin represents the eremitical tradition in Irish monasticism. Glendalough became one of Ireland\'s most important monastic cities.',
        'tradition' => 'Pilgrimage to Glendalough, particularly to St. Kevin\'s Bed (a cave) and the Round Tower.',
        'related_sites' => 'Glendalough Monastic Site, St. Kevin\'s Church, Round Tower',
    ),
    'saint-columba-colmcille-abbot' => array(
        'full_name' => 'Saint Columba (Colmcille), Abbot',
        'description' => 'Columba (521-597 AD), also known as Colmcille ("Dove of the Church"), was an Irish monk who founded the monastery on Iona and is credited with converting much of Scotland to Christianity.',
        'significance' => 'One of Ireland\'s three patron saints, Columba spread Christianity throughout Scotland and northern Britain. He was also a noted scholar and scribe.',
        'tradition' => 'Pilgrimage to Iona, Scotland, and to Derry and Kells in Ireland. Columba is patron of poets and bookbinders.',
        'related_sites' => 'Iona Abbey (Scotland), St. Columba\'s House Kells, St. Columba\'s Church Derry',
    ),
    'the-blessed-irish-martyrs' => array(
        'full_name' => 'The Blessed Irish Martyrs',
        'description' => 'This memorial honors 17 Irish men and women who were martyred for their Catholic faith during the Reformation persecutions in the 16th and 17th centuries. They were beatified by Pope John Paul II in 1992.',
        'significance' => 'These martyrs represent the thousands of Irish Catholics who suffered persecution, torture, and death rather than renounce their faith.',
        'tradition' => 'Special Masses and prayers for the martyrs, reflection on religious freedom.',
    ),
    'saint-oliver-plunkett-bishop-and-martyr' => array(
        'full_name' => 'Saint Oliver Plunkett, Bishop and Martyr',
        'description' => 'Oliver Plunkett (1625-1681) was the Archbishop of Armagh and Primate of All Ireland. He was falsely accused of treason, tried in London, and became the last Catholic martyr to die at Tyburn.',
        'significance' => 'Oliver Plunkett was canonized in 1975, the first new Irish saint in almost 700 years. He represents courage in the face of persecution.',
        'tradition' => 'Pilgrimage to St. Peter\'s Church in Drogheda where his preserved head is venerated.',
        'related_sites' => 'St. Peter\'s Church Drogheda, Downpatrick Cathedral',
    ),
    'our-lady-of-knock' => array(
        'full_name' => 'Our Lady of Knock',
        'description' => 'On August 21, 1879, fifteen people witnessed an apparition of Mary, St. Joseph, and St. John the Evangelist at the parish church in Knock, County Mayo.',
        'significance' => 'Knock became Ireland\'s National Marian Shrine. The apparition came during difficult times of famine and emigration, offering hope to the Irish people.',
        'tradition' => 'Pilgrimage to Knock Shrine, particularly on August 17th and throughout the year. Pope John Paul II and Pope Francis both visited Knock.',
        'related_sites' => 'Knock Shrine and Basilica, County Mayo',
    ),
    'all-saints-of-ireland' => array(
        'full_name' => 'All Saints of Ireland',
        'description' => 'This feast celebrates all the saints of Ireland, both known and unknown, who have lived holy lives and contributed to the faith.',
        'significance' => 'Ireland has produced countless saints throughout history - from the early monastic period through modern times. This day honors the rich spiritual heritage of the Emerald Isle.',
        'tradition' => 'Remembrance of Irish missionaries who spread the faith throughout Europe (the "Island of Saints and Scholars").',
    ),
);

// Check if viewing a feast detail page
if ($feast_slug && isset($feast_descriptions[$feast_slug])) {
    include(get_stylesheet_directory() . '/includes/calendar-feast-detail.php');
    get_footer();
    return;
}

// Get current month and day for "today" highlighting
$current_month = intval(date('n'));
$current_day = intval(date('j'));
$current_year = intval(date('Y'));
?>

<main id="main" class="site-main calendar-page">

    <!-- Calendar Header -->
    <section class="calendar-header">
        <div class="container">
            <h1 class="calendar-title">Irish Catholic Liturgical Calendar <?php echo esc_html($year); ?></h1>
            <p class="calendar-description">Celebrate Ireland's rich Catholic tradition with feast days, solemnities, and commemorations of Irish saints throughout the year</p>

            <!-- Year Selector -->
            <div class="year-selector">
                <a href="?year=2025" class="year-btn <?php echo $year == 2025 ? 'active' : ''; ?>">2025</a>
                <a href="?year=2026" class="year-btn <?php echo $year == 2026 ? 'active' : ''; ?>">2026</a>
            </div>
        </div>
    </section>

    <!-- Month Navigation (Sticky) -->
    <nav class="month-nav">
        <div class="container">
            <div class="month-nav-inner">
                <?php foreach ($months as $month_num => $month_data): ?>
                    <a href="#month-<?php echo $month_num; ?>" class="month-nav-btn <?php echo ($year == $current_year && $month_num == $current_month) ? 'active' : ''; ?>">
                        <?php echo substr($month_data['name'], 0, 3); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <!-- Legend Section -->
    <section class="calendar-legend">
        <div class="container">
            <h2>Liturgical Ranks & Colors</h2>
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="rank-badge solemnity">Solemnity</span>
                    <p>Highest rank - Major celebrations</p>
                </div>
                <div class="legend-item">
                    <span class="rank-badge feast">Feast</span>
                    <p>Important celebrations</p>
                </div>
                <div class="legend-item">
                    <span class="rank-badge memorial">Memorial</span>
                    <p>Remembrance of saints</p>
                </div>
                <div class="legend-item">
                    <span class="irish-badge">★ Irish Saint</span>
                    <p>Special Irish celebrations</p>
                </div>
            </div>

            <h3>Liturgical Colors</h3>
            <div class="colors-grid">
                <?php foreach ($liturgical_colors as $color_key => $color_info): ?>
                    <div class="color-item">
                        <span class="color-dot color-<?php echo $color_key; ?>"></span>
                        <span><strong><?php echo esc_html($color_info['name']); ?>:</strong> <?php echo esc_html($color_info['meaning']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Calendar Grid -->
    <section class="calendar-content">
        <div class="container">
            <div class="months-grid">
                <?php foreach ($months as $month_num => $month_data): ?>
                    <div class="month-card" id="month-<?php echo $month_num; ?>">
                        <h3 class="month-name"><?php echo $month_data['name']; ?> <?php echo esc_html($year); ?></h3>

                        <?php if (empty($month_data['days'])): ?>
                            <p class="no-special-days">No special feast days listed this month</p>
                        <?php else: ?>
                            <div class="feast-days-list">
                                <?php foreach ($month_data['days'] as $day_num => $day_data):
                                    $feast_slug = sanitize_title($day_data['name']);
                                    $has_detail = isset($feast_descriptions[$feast_slug]);
                                    $feast_url = $has_detail ? '?year=' . $year . '&feast=' . $feast_slug : '#';
                                    $is_today = ($year == $current_year && $month_num == $current_month && $day_num == $current_day);
                                ?>
                                    <a href="<?php echo $feast_url; ?>" class="feast-day-item <?php echo isset($day_data['irish']) ? 'irish-feast' : ''; ?> <?php echo $has_detail ? 'has-detail' : 'no-detail'; ?> <?php echo $is_today ? 'is-today' : ''; ?>">
                                        <div class="feast-date">
                                            <span class="date-number"><?php echo $day_num; ?></span>
                                            <span class="color-indicator color-<?php echo $day_data['color']; ?>"></span>
                                        </div>
                                        <div class="feast-details">
                                            <div class="feast-name">
                                                <?php echo esc_html($day_data['name']); ?>
                                                <?php if (isset($day_data['irish'])): ?>
                                                    <span class="irish-star">★</span>
                                                <?php endif; ?>
                                                <?php if ($is_today): ?>
                                                    <span class="today-badge">Today</span>
                                                <?php endif; ?>
                                                <?php if ($has_detail): ?>
                                                    <span class="detail-arrow">→</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="feast-meta">
                                                <span class="rank-badge <?php echo $day_data['rank']; ?>">
                                                    <?php echo ucfirst($day_data['rank']); ?>
                                                </span>
                                                <?php if (isset($day_data['season'])): ?>
                                                    <span class="season-badge"><?php echo ucfirst($day_data['season']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Irish Saints Section -->
    <section class="irish-saints-section">
        <div class="container">
            <h2>Irish Saints Celebrated in <?php echo esc_html($year); ?></h2>
            <p class="section-intro">Ireland's patron saints and martyrs are honored throughout the liturgical year</p>

            <div class="saints-grid">
                <?php
                $saint_descriptions = array(
                    'Saint Patrick' => 'Patron Saint of Ireland, apostle who brought Christianity to the Emerald Isle',
                    'Saint Brigid' => 'Abbess of Kildare, one of Ireland\'s three patron saints alongside Patrick and Columba',
                    'Saint Columba' => 'Colmcille - Founder of Iona monastery, missionary to Scotland',
                    'Saint Kevin' => 'Founder of Glendalough monastery, hermit and abbot',
                    'Saint Ita' => 'Foster-mother of the saints of Ireland, founded convent in Limerick',
                    'Saint Oliver Plunkett' => 'Archbishop of Armagh, martyr during Reformation persecutions',
                    'The Blessed Irish Martyrs' => 'Irish men and women martyred for their faith',
                    'All Saints of Ireland' => 'Collective celebration of all Irish saints known and unknown'
                );

                foreach ($irish_saints as $saint):
                    $description = isset($saint_descriptions[$saint]) ? $saint_descriptions[$saint] : 'Honored in Irish Catholic tradition';
                ?>
                    <div class="saint-card">
                        <h4><?php echo esc_html($saint); ?></h4>
                        <p><?php echo esc_html($description); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Link to Sacred Sites -->
            <div class="calendar-cta">
                <h3>Visit the Sacred Sites of Irish Saints</h3>
                <p>Explore the monasteries, churches, and holy places associated with Ireland's beloved saints</p>
                <div class="cta-buttons">
                    <a href="<?php echo home_url('/monastic-sites/'); ?>" class="btn-primary">Monastic Sites</a>
                    <a href="<?php echo home_url('/county/'); ?>" class="btn-secondary">Browse by County</a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
// Smooth scroll for month navigation
document.querySelectorAll('.month-nav-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (this.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Update active state
                document.querySelectorAll('.month-nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        }
    });
});

// Highlight current month in nav on scroll
const monthCards = document.querySelectorAll('.month-card');
const monthNavBtns = document.querySelectorAll('.month-nav-btn:not(.ical-export-btn)');

const observerOptions = {
    root: null,
    rootMargin: '-100px 0px -50% 0px',
    threshold: 0
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const monthId = entry.target.id;
            monthNavBtns.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('href') === '#' + monthId);
            });
        }
    });
}, observerOptions);

monthCards.forEach(card => observer.observe(card));
</script>

<?php get_footer(); ?>
