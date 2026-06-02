<?php
return [

    /*
     * Which countries import
     */
    'countries' => [
        'RU',
        'UA',
        'DE',
    ],

    /*
     * Import Regions
     */
    'regions' => true,

    /*
     * Import Cities
     */
    'cities' => true,

    'sources' => [
        'countries' => 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/xml/countries.xml',
        'regions' => 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/xml/states.xml',
        'cities' => 'https://github.com/dr5hn/countries-states-cities-database/releases/latest/download/xml-cities.xml.gz',
    ],
];
