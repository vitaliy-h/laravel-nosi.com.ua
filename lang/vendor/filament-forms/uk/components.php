<?php

// Fills keys missing from Filament's bundled uk translations (merged over the vendor file).

return [
    'color_picker' => [
        'panel_label' => 'Вибір кольору',
    ],
    'date_time_picker' => [
        'month_select' => ['label' => 'Місяць'],
        'year_input'   => ['label' => 'Рік'],
        'hour_input'   => ['label' => 'Година'],
        'minute_input' => ['label' => 'Хвилина'],
        'second_input' => ['label' => 'Секунда'],
    ],
    'file_upload' => [
        'actions' => [
            'download' => ['label' => 'Завантажити'],
            'open'     => ['label' => 'Відкрити в новій вкладці'],
        ],
        'editor' => [
            'label' => 'Редактор зображень',
        ],
    ],
    'key_value' => [
        'columns' => [
            'actions' => ['label' => 'Дії'],
            'reorder' => ['label' => 'Змінити порядок'],
        ],
    ],
    'repeater' => [
        'columns' => [
            'actions' => ['label' => 'Дії'],
            'reorder' => ['label' => 'Змінити порядок'],
        ],
    ],
    'rich_editor' => [
        'actions' => [
            'text_color' => [
                'modal' => [
                    'form' => [
                        'color' => [
                            'options' => [
                                'slate'   => 'Сланцевий',
                                'gray'    => 'Сірий',
                                'zinc'    => 'Цинковий',
                                'neutral' => 'Нейтральний',
                                'stone'   => 'Кам’яний',
                                'mauve'   => 'Ліловий',
                                'olive'   => 'Оливковий',
                                'mist'    => 'Туманний',
                                'taupe'   => 'Сіро-коричневий',
                                'red'     => 'Червоний',
                                'orange'  => 'Помаранчевий',
                                'amber'   => 'Бурштиновий',
                                'yellow'  => 'Жовтий',
                                'lime'    => 'Лаймовий',
                                'green'   => 'Зелений',
                                'emerald' => 'Смарагдовий',
                                'teal'    => 'Бірюзовий',
                                'cyan'    => 'Блакитний',
                                'sky'     => 'Небесний',
                                'blue'    => 'Синій',
                                'indigo'  => 'Індиго',
                                'violet'  => 'Фіалковий',
                                'purple'  => 'Пурпуровий',
                                'fuchsia' => 'Фуксія',
                                'pink'    => 'Рожевий',
                                'rose'    => 'Трояндовий',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'mentions' => [
            'no_options_message'        => 'Немає доступних варіантів.',
            'no_search_results_message' => 'Нічого не знайдено.',
            'search_prompt'             => 'Почніть вводити для пошуку...',
            'searching_message'         => 'Пошук...',
        ],
        'toolbar' => [
            'label' => 'Панель інструментів редактора',
        ],
        'tools' => [
            'h4'                       => 'Заголовок 4',
            'h5'                       => 'Заголовок 5',
            'h6'                       => 'Заголовок 6',
            'paragraph'                => 'Абзац',
            'table_toggle_header_cell' => 'Перемкнути клітинку заголовка',
        ],
    ],
    'select' => [
        'actions' => [
            'clear'         => ['label' => 'Очистити вибір'],
            'remove_option' => ['label' => 'Прибрати :label'],
        ],
        'no_options_message' => 'Немає доступних варіантів.',
        'search_label'       => 'Пошук',
    ],
    'tags_input' => [
        'actions' => [
            'delete' => ['label' => 'Видалити'],
        ],
        'tag_added'   => 'Додано: :tag',
        'tag_removed' => 'Видалено: :tag',
    ],
];
