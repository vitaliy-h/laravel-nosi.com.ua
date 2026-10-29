<?php

/*
|--------------------------------------------------------------------------
| Site system messages
|--------------------------------------------------------------------------
|
| Fixed texts of the site that are not edited in the admin panel (unlike
| dictionary.php): form attributes and notices, API and social login messages.
| Used as __('site.{group}.{key}').
|
*/

return [

    // New-order notification sent to the managers' Telegram chat.
    'telegram_order' => [
        'title'    => 'Нове замовлення №:number',
        'customer' => 'Клієнт',
        'phone'    => 'Телефон',
        'email'    => 'Email',
        'comment'  => 'Коментар',
        'items'    => 'Товари',
        'discount' => 'Знижка',
        'total'    => 'Сума',
    ],

    // The bot's reply to whoever writes to it (TelegramWebhookController).
    'telegram_bot' => [
        'chat_id' => "Ваш chat ID: <code>:id</code>\n\nВставте його в адмінці: Мови → Українська → «Telegram-сповіщення про замовлення». Кількох одержувачів — через кому.",
    ],

    'feedback' => [
        'name'    => 'Ім’я',
        'email'   => 'Електронна пошта',
        'phone'   => 'Телефон',
        'message' => 'Повідомлення',
        'saved'   => 'Ваше повідомлення надіслано.',
    ],

    'news' => [
        'back' => 'Усі новини',
    ],

    'subscribe' => [
        'email'   => 'Електронна пошта',
        'saved'   => 'Дякуємо за підписку!',
        'already' => 'Цей email уже підписаний.',
    ],

    'api' => [
        'validation_error'   => 'Помилка валідації.',
        'unauthenticated'    => 'Потрібна авторизація.',
        'forbidden'          => 'Доступ заборонено.',
        'resource_not_found' => 'Ресурс не знайдено.',
        'to_many_requests'   => 'Забагато запитів.',
        'login_success'      => 'Вхід виконано.',
        'register_success'   => 'Реєстрація успішна.',
        'logout_success'     => 'Ви вийшли з акаунта.',
    ],

    'social' => [
        'section_title'                        => 'Соцмережі',
        'section_subtitle'                     => 'Під’єднайте акаунти соцмереж, щоб входити швидше.',
        'connect'                              => 'Під’єднати',
        'disconnect'                           => 'Від’єднати',
        'connected'                            => 'Під’єднано (:identifier)',
        'disconnected'                         => 'Акаунт соцмережі від’єднано.',
        'auth_failed'                          => 'Не вдалося увійти через соцмережу. Спробуйте ще раз.',
        'cannot_disconnect_last'               => 'Не можна від’єднати єдиний спосіб входу. Спочатку додайте пароль або email.',
        'already_connected_to_another_account' => 'Цей акаунт соцмережі вже під’єднано до іншого користувача.',
        'sign_in_with'                         => 'Увійти через :provider',
        'google'                               => 'Google',
        'facebook'                             => 'Facebook',
        'telegram'                             => 'Telegram',
    ],

];
