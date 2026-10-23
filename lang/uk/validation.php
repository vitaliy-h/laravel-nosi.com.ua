<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted'        => 'Поле «:attribute» має бути прийняте.',
    'accepted_if'     => 'Поле «:attribute» має бути прийняте, коли :other має значення :value.',
    'active_url'      => 'Поле «:attribute» має містити дійсний URL.',
    'after'           => 'Поле «:attribute» має містити дату після :date.',
    'after_or_equal'  => 'Поле «:attribute» має містити дату не раніше :date.',
    'alpha'           => 'Поле «:attribute» може містити лише літери.',
    'alpha_dash'      => 'Поле «:attribute» може містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num'       => 'Поле «:attribute» може містити лише літери та цифри.',
    'any_of'          => 'Поле «:attribute» має недійсне значення.',
    'array'           => 'Поле «:attribute» має бути масивом.',
    'ascii'           => 'Поле «:attribute» може містити лише однобайтові латинські символи, цифри та знаки.',
    'before'          => 'Поле «:attribute» має містити дату до :date.',
    'before_or_equal' => 'Поле «:attribute» має містити дату не пізніше :date.',
    'between'         => [
        'array'   => 'Поле «:attribute» має містити від :min до :max елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути від :min до :max КБ.',
        'numeric' => 'Значення поля «:attribute» має бути від :min до :max.',
        'string'  => 'Довжина поля «:attribute» має бути від :min до :max символів.',
    ],
    'boolean'           => 'Поле «:attribute» має бути true або false.',
    'can'               => 'Поле «:attribute» містить недозволене значення.',
    'confirmed'         => 'Підтвердження поля «:attribute» не збігається.',
    'contains'          => 'У полі «:attribute» бракує обов’язкового значення.',
    'current_password'  => 'Невірний пароль.',
    'date'              => 'Поле «:attribute» має містити дійсну дату.',
    'date_equals'       => 'Поле «:attribute» має містити дату :date.',
    'date_format'       => 'Поле «:attribute» має відповідати формату :format.',
    'decimal'           => 'Поле «:attribute» має містити :decimal знаків після коми.',
    'declined'          => 'Поле «:attribute» має бути відхилене.',
    'declined_if'       => 'Поле «:attribute» має бути відхилене, коли :other має значення :value.',
    'different'         => 'Поля «:attribute» і :other мають відрізнятися.',
    'digits'            => 'Поле «:attribute» має містити :digits цифр.',
    'digits_between'    => 'Поле «:attribute» має містити від :min до :max цифр.',
    'dimensions'        => 'Зображення в полі «:attribute» має недопустимі розміри.',
    'distinct'          => 'Поле «:attribute» містить значення, що повторюється.',
    'doesnt_contain'    => 'Поле «:attribute» не має містити жодного з таких значень: :values.',
    'doesnt_end_with'   => 'Поле «:attribute» не має закінчуватися жодним із таких значень: :values.',
    'doesnt_start_with' => 'Поле «:attribute» не має починатися з жодного з таких значень: :values.',
    'email'             => 'Поле «:attribute» має містити дійсну email-адресу.',
    'encoding'          => 'Поле «:attribute» має бути в кодуванні :encoding.',
    'ends_with'         => 'Поле «:attribute» має закінчуватися одним із таких значень: :values.',
    'enum'              => 'Вибране значення поля «:attribute» недійсне.',
    'exists'            => 'Вибране значення поля «:attribute» недійсне.',
    'extensions'        => 'Файл у полі «:attribute» має мати одне з розширень: :values.',
    'file'              => 'Поле «:attribute» має бути файлом.',
    'filled'            => 'Поле «:attribute» має бути заповнене.',
    'gt'                => [
        'array'   => 'Поле «:attribute» має містити більше ніж :value елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути більшим за :value КБ.',
        'numeric' => 'Значення поля «:attribute» має бути більшим за :value.',
        'string'  => 'Довжина поля «:attribute» має бути більшою за :value символів.',
    ],
    'gte' => [
        'array'   => 'Поле «:attribute» має містити :value або більше елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути не меншим за :value КБ.',
        'numeric' => 'Значення поля «:attribute» має бути не меншим за :value.',
        'string'  => 'Довжина поля «:attribute» має бути не меншою за :value символів.',
    ],
    'hex_color'     => 'Поле «:attribute» має містити дійсний шістнадцятковий колір.',
    'image'         => 'Поле «:attribute» має бути зображенням.',
    'in'            => 'Вибране значення поля «:attribute» недійсне.',
    'in_array'      => 'Значення поля «:attribute» має існувати в :other.',
    'in_array_keys' => 'Поле «:attribute» має містити принаймні один із ключів: :values.',
    'integer'       => 'Поле «:attribute» має бути цілим числом.',
    'ip'            => 'Поле «:attribute» має містити дійсну IP-адресу.',
    'ipv4'          => 'Поле «:attribute» має містити дійсну IPv4-адресу.',
    'ipv6'          => 'Поле «:attribute» має містити дійсну IPv6-адресу.',
    'json'          => 'Поле «:attribute» має містити дійсний JSON-рядок.',
    'list'          => 'Поле «:attribute» має бути списком.',
    'lowercase'     => 'Поле «:attribute» має бути в нижньому регістрі.',
    'lt'            => [
        'array'   => 'Поле «:attribute» має містити менше ніж :value елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути меншим за :value КБ.',
        'numeric' => 'Значення поля «:attribute» має бути меншим за :value.',
        'string'  => 'Довжина поля «:attribute» має бути меншою за :value символів.',
    ],
    'lte' => [
        'array'   => 'Поле «:attribute» не має містити більше ніж :value елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути не більшим за :value КБ.',
        'numeric' => 'Значення поля «:attribute» має бути не більшим за :value.',
        'string'  => 'Довжина поля «:attribute» має бути не більшою за :value символів.',
    ],
    'mac_address' => 'Поле «:attribute» має містити дійсну MAC-адресу.',
    'max'         => [
        'array'   => 'Поле «:attribute» не має містити більше ніж :max елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» не має перевищувати :max КБ.',
        'numeric' => 'Значення поля «:attribute» не має перевищувати :max.',
        'string'  => 'Довжина поля «:attribute» не має перевищувати :max символів.',
    ],
    'max_digits' => 'Поле «:attribute» не має містити більше ніж :max цифр.',
    'mimes'      => 'Файл у полі «:attribute» має бути одного з типів: :values.',
    'mimetypes'  => 'Файл у полі «:attribute» має бути одного з типів: :values.',
    'min'        => [
        'array'   => 'Поле «:attribute» має містити щонайменше :min елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути щонайменше :min КБ.',
        'numeric' => 'Значення поля «:attribute» має бути щонайменше :min.',
        'string'  => 'Довжина поля «:attribute» має бути щонайменше :min символів.',
    ],
    'min_digits'       => 'Поле «:attribute» має містити щонайменше :min цифр.',
    'missing'          => 'Поле «:attribute» має бути відсутнім.',
    'missing_if'       => 'Поле «:attribute» має бути відсутнім, коли :other має значення :value.',
    'missing_unless'   => 'Поле «:attribute» має бути відсутнім, якщо :other не має значення :value.',
    'missing_with'     => 'Поле «:attribute» має бути відсутнім, коли присутнє :values.',
    'missing_with_all' => 'Поле «:attribute» має бути відсутнім, коли присутні :values.',
    'multiple_of'      => 'Значення поля «:attribute» має бути кратним :value.',
    'not_in'           => 'Вибране значення поля «:attribute» недійсне.',
    'not_regex'        => 'Поле «:attribute» має недійсний формат.',
    'numeric'          => 'Поле «:attribute» має бути числом.',
    'password'         => [
        'letters'       => 'Поле «:attribute» має містити принаймні одну літеру.',
        'mixed'         => 'Поле «:attribute» має містити принаймні одну велику й одну малу літеру.',
        'numbers'       => 'Поле «:attribute» має містити принаймні одну цифру.',
        'symbols'       => 'Поле «:attribute» має містити принаймні один спецсимвол.',
        'uncompromised' => 'Значення поля «:attribute» знайдено у витоку даних. Виберіть інше значення.',
    ],
    'present'                => 'Поле «:attribute» має бути присутнім.',
    'present_if'             => 'Поле «:attribute» має бути присутнім, коли :other має значення :value.',
    'present_unless'         => 'Поле «:attribute» має бути присутнім, якщо :other не має значення :value.',
    'present_with'           => 'Поле «:attribute» має бути присутнім, коли присутнє :values.',
    'present_with_all'       => 'Поле «:attribute» має бути присутнім, коли присутні :values.',
    'prohibited'             => 'Поле «:attribute» заборонене.',
    'prohibited_if'          => 'Поле «:attribute» заборонене, коли :other має значення :value.',
    'prohibited_if_accepted' => 'Поле «:attribute» заборонене, коли :other прийнято.',
    'prohibited_if_declined' => 'Поле «:attribute» заборонене, коли :other відхилено.',
    'prohibited_unless'      => 'Поле «:attribute» заборонене, якщо :other не входить до :values.',
    'prohibits'              => 'Поле «:attribute» забороняє присутність :other.',
    'regex'                  => 'Поле «:attribute» має недійсний формат.',
    'required'               => 'Поле «:attribute» обов’язкове.',
    'required_array_keys'    => 'Поле «:attribute» має містити записи для: :values.',
    'required_if'            => 'Поле «:attribute» обов’язкове, коли :other має значення :value.',
    'required_if_accepted'   => 'Поле «:attribute» обов’язкове, коли :other прийнято.',
    'required_if_declined'   => 'Поле «:attribute» обов’язкове, коли :other відхилено.',
    'required_unless'        => 'Поле «:attribute» обов’язкове, якщо :other не входить до :values.',
    'required_with'          => 'Поле «:attribute» обов’язкове, коли присутнє :values.',
    'required_with_all'      => 'Поле «:attribute» обов’язкове, коли присутні :values.',
    'required_without'       => 'Поле «:attribute» обов’язкове, коли відсутнє :values.',
    'required_without_all'   => 'Поле «:attribute» обов’язкове, коли відсутні всі з :values.',
    'same'                   => 'Поле «:attribute» має збігатися з :other.',
    'size'                   => [
        'array'   => 'Поле «:attribute» має містити :size елементів.',
        'file'    => 'Розмір файлу в полі «:attribute» має бути :size КБ.',
        'numeric' => 'Значення поля «:attribute» має бути :size.',
        'string'  => 'Довжина поля «:attribute» має бути :size символів.',
    ],
    'starts_with' => 'Поле «:attribute» має починатися з одного з таких значень: :values.',
    'string'      => 'Поле «:attribute» має бути рядком.',
    'timezone'    => 'Поле «:attribute» має містити дійсний часовий пояс.',
    'unique'      => 'Таке значення поля «:attribute» уже існує.',
    'uploaded'    => 'Не вдалося завантажити файл у поле «:attribute».',
    'uppercase'   => 'Поле «:attribute» має бути у верхньому регістрі.',
    'url'         => 'Поле «:attribute» має містити дійсний URL.',
    'ulid'        => 'Поле «:attribute» має містити дійсний ULID.',
    'uuid'        => 'Поле «:attribute» має містити дійсний UUID.',
    'ua_phone'    => 'Поле «:attribute» має містити український номер, наприклад +380 67 123 45 67.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
