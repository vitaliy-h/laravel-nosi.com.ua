<?php

use App\Modules\Notification\Enums\LetterEnum;

return [

    LetterEnum::FEEDBACK->value => [
        'subject' => 'Нове повідомлення зі зворотного зв’язку',
        'body'    => '
        <h2>Нове повідомлення з сайту</h2>

        <p>
        Ім’я: {{ $user_name }}<br>
        Email: {{ $user_email }}<br>
        Телефон: {{ $user_phone }}
        </p>

        <p>Повідомлення:<br>{{ $message }}</p>
        ',
    ],

    LetterEnum::PASSWORD_RESET->value => [
        'subject' => 'Відновлення пароля',
        'body'    => '
        <h2>Відновлення пароля</h2>

        <p>
        Ви отримали цей лист, бо надійшов запит на скидання пароля до вашого акаунта.
        </p>

        <p>
        <a href="{{ $reset_url }}">
        Змінити пароль
        </a>
        </p>

        <p>
        Якщо ви не надсилали такого запиту, просто проігноруйте цей лист.
        </p>
        ',
    ],

    LetterEnum::REGISTRATION->value => [
        'subject' => 'Ласкаво просимо! Дані вашого акаунта',
        'body'    => '
        <h2>Вітаємо, {{ $user_name }}!</h2>

        <p>
        Ваш акаунт створено.
        </p>

        <p>
        Email: {{ $user_email }}<br>
        Пароль: {{ $user_password }}
        </p>

        <p>
        <a href="{{ $login_url }}">
        Увійти
        </a>
        </p>
        ',
    ],

    LetterEnum::ORDER_CREATED->value => [
        'subject' => 'Замовлення №{{ $order_number }} оформлено',
        'body'    => '
        <h2>Дякуємо за замовлення!</h2>

        <p>
        Номер замовлення: {{ $order_number }}<br>
        Сума: {{ $total_amount }}
        </p>

        <p>
        Ми зв’яжемося з вами найближчим часом, щоб підтвердити замовлення.
        </p>

        <p>
        <a href="{{ $order_url }}">
        Переглянути замовлення
        </a>
        </p>
        ',
    ],

];
