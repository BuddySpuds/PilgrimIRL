<?php
/**
 * Irish Catholic Liturgical Calendar 2026
 * Data source: https://gcatholic.org/calendar/2026/IE-en
 */

if (!defined('ABSPATH')) {
    exit;
}

return array(
    'year' => 2026,
    'months' => array(
        1 => array(
            'name' => 'January',
            'days' => array(
                1 => array('name' => 'The Blessed Virgin Mary, the Mother of God', 'rank' => 'solemnity', 'color' => 'white'),
                6 => array('name' => 'The Epiphany of the Lord', 'rank' => 'solemnity', 'color' => 'white'),
                15 => array('name' => 'Saint Ita of Limerick, Virgin', 'rank' => 'memorial', 'color' => 'white', 'irish' => true),
            )
        ),
        2 => array(
            'name' => 'February',
            'days' => array(
                1 => array('name' => 'Saint Brigid of Kildare', 'rank' => 'feast', 'color' => 'white', 'irish' => true),
                2 => array('name' => 'The Presentation of the Lord', 'rank' => 'feast', 'color' => 'white'),
            )
        ),
        3 => array(
            'name' => 'March',
            'days' => array(
                17 => array('name' => 'Saint Patrick, Bishop, Patron of Ireland', 'rank' => 'solemnity', 'color' => 'white', 'irish' => true),
                19 => array('name' => 'Saint Joseph, Husband of Mary', 'rank' => 'solemnity', 'color' => 'white'),
                25 => array('name' => 'The Annunciation of the Lord', 'rank' => 'solemnity', 'color' => 'white'),
            )
        ),
        4 => array(
            'name' => 'April',
            'days' => array(
                5 => array('name' => 'Easter Sunday', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                6 => array('name' => 'Monday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                7 => array('name' => 'Tuesday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                8 => array('name' => 'Wednesday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                9 => array('name' => 'Thursday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                10 => array('name' => 'Friday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
                11 => array('name' => 'Saturday within the Octave of Easter', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'easter'),
            )
        ),
        5 => array(
            'name' => 'May',
            'days' => array(
                17 => array('name' => 'Ascension of the Lord', 'rank' => 'solemnity', 'color' => 'white'),
                24 => array('name' => 'Pentecost Sunday', 'rank' => 'solemnity', 'color' => 'red'),
                31 => array('name' => 'The Most Holy Trinity', 'rank' => 'solemnity', 'color' => 'white'),
            )
        ),
        6 => array(
            'name' => 'June',
            'days' => array(
                3 => array('name' => 'Saint Kevin, Abbot', 'rank' => 'memorial', 'color' => 'white', 'irish' => true),
                7 => array('name' => 'The Most Holy Body and Blood of Christ', 'rank' => 'solemnity', 'color' => 'white'),
                9 => array('name' => 'Saint Columba (Colmcille), Abbot', 'rank' => 'memorial', 'color' => 'white', 'irish' => true),
                12 => array('name' => 'The Most Sacred Heart of Jesus', 'rank' => 'solemnity', 'color' => 'white'),
                20 => array('name' => 'The Blessed Irish Martyrs', 'rank' => 'memorial', 'color' => 'red', 'irish' => true),
                24 => array('name' => 'The Nativity of Saint John the Baptist', 'rank' => 'solemnity', 'color' => 'white'),
                29 => array('name' => 'Saint Peter and Saint Paul, Apostles', 'rank' => 'solemnity', 'color' => 'red'),
            )
        ),
        7 => array(
            'name' => 'July',
            'days' => array(
                1 => array('name' => 'Saint Oliver Plunkett, Bishop and Martyr', 'rank' => 'memorial', 'color' => 'red', 'irish' => true),
            )
        ),
        8 => array(
            'name' => 'August',
            'days' => array(
                15 => array('name' => 'The Assumption of the Blessed Virgin Mary', 'rank' => 'solemnity', 'color' => 'white'),
                17 => array('name' => 'Our Lady of Knock', 'rank' => 'memorial', 'color' => 'white', 'irish' => true),
            )
        ),
        9 => array(
            'name' => 'September',
            'days' => array(
                14 => array('name' => 'The Exaltation of the Holy Cross', 'rank' => 'feast', 'color' => 'red'),
            )
        ),
        10 => array(
            'name' => 'October',
            'days' => array()
        ),
        11 => array(
            'name' => 'November',
            'days' => array(
                1 => array('name' => 'All Saints', 'rank' => 'solemnity', 'color' => 'white'),
                2 => array('name' => 'All Souls', 'rank' => 'commemoration', 'color' => 'violet'),
                6 => array('name' => 'All Saints of Ireland', 'rank' => 'feast', 'color' => 'white', 'irish' => true),
                22 => array('name' => 'Our Lord Jesus Christ, King of the Universe', 'rank' => 'solemnity', 'color' => 'white'),
            )
        ),
        12 => array(
            'name' => 'December',
            'days' => array(
                8 => array('name' => 'The Immaculate Conception of the Blessed Virgin Mary', 'rank' => 'solemnity', 'color' => 'white'),
                25 => array('name' => 'The Nativity of the Lord (Christmas)', 'rank' => 'solemnity', 'color' => 'white', 'season' => 'christmas'),
            )
        ),
    ),
    'irish_saints' => array(
        'Saint Patrick',
        'Saint Brigid',
        'Saint Columba',
        'Saint Kevin',
        'Saint Ita',
        'Saint Oliver Plunkett',
        'The Blessed Irish Martyrs',
        'All Saints of Ireland'
    ),
    'liturgical_colors' => array(
        'white' => array('name' => 'White', 'meaning' => 'Joy, purity, glory'),
        'red' => array('name' => 'Red', 'meaning' => 'Passion, fire of the Holy Spirit, martyrdom'),
        'green' => array('name' => 'Green', 'meaning' => 'Ordinary Time, hope, growth'),
        'violet' => array('name' => 'Violet/Purple', 'meaning' => 'Penance, preparation'),
        'rose' => array('name' => 'Rose', 'meaning' => 'Joy in the midst of penance'),
    )
);
