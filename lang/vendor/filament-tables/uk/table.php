<?php

// Fills keys missing from Filament's bundled uk translations (merged over the vendor file).

return [
    'column_manager' => [
        'actions' => [
            'reorder' => ['label' => 'Перемістити колонку'],
        ],
    ],
    'columns' => [
        'icon' => [
            'boolean' => [
                'true'  => 'Так',
                'false' => 'Ні',
            ],
        ],
        'select' => [
            'no_options_message' => 'Немає доступних варіантів.',
        ],
    ],
    'actions' => [
        'reorder_record'        => ['label' => 'Перемістити запис :key'],
        'toggle_record_content' => ['label' => 'Розгорнути/згорнути запис :key'],
    ],
    'loading'      => 'Завантаження...',
    'result_count' => '{0} Немає результатів|[1,*] Результатів: :count',
];
