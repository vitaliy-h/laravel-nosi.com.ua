<?php

// Fills keys missing from Filament's bundled uk translations (merged over the vendor file).

return [
    'callout' => [
        'statuses' => [
            'danger'  => 'Помилка:',
            'info'    => 'Примітка:',
            'success' => 'Успіх:',
            'warning' => 'Увага:',
        ],
    ],
    'section' => [
        'actions' => [
            'collapse' => ['label' => 'Згорнути розділ'],
            'expand'   => ['label' => 'Розгорнути розділ'],
        ],
    ],
    'wizard' => [
        'header' => [
            'step' => [
                'statuses' => [
                    'completed' => 'Завершено',
                    'upcoming'  => 'Не завершено',
                ],
            ],
        ],
    ],
];
