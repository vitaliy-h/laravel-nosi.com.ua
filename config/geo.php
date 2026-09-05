<?php

return [

    /*
        * Which countries to import.
        * Enabled by default:
        * - United Kingdom (GB)
        *
        * Common country codes:
        * AU - Australia
        * CA - Canada
        * CH - Switzerland
        * CN - China
        * DE - Germany
        * ES - Spain
        * FR - France
        * GB - United Kingdom
        * IE - Ireland
        * IN - India
        * IT - Italy
        * JP - Japan
        * NL - Netherlands
        * NZ - New Zealand
        * PL - Poland
        * SE - Sweden
        * UA - Ukraine
        * US - United States
        * RU - Russia
        */
    'countries' => [
        'GB',

        // 'AU',
        // 'CA',
        // 'CH',
        // 'CN',
        // 'DE',
        // 'ES',
        // 'FR',
        // 'IE',
        // 'IN',
        // 'IT',
        // 'JP',
        // 'NL',
        // 'NZ',
        // 'PL',
        // 'SE',
        // 'UA',
        // 'US',
        // 'RU',
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
        'regions'   => 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/xml/states.xml',
        'cities'    => 'https://github.com/dr5hn/countries-states-cities-database/releases/latest/download/xml-cities.xml.gz',
    ],
];
