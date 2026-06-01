<?php

// ============================================
// AJAX test page + API endpoints
// Put this file into project root:
// /ajax-test.php
// ============================================

// ============================================
// API responses
// ============================================

if (isset($_GET['api'])) {

    header('Content-Type: application/json');

    $response = [
        'success' => true,
        'actions' => [],
    ];

    switch ($_GET['api']) {

        case 'html':

            $response['actions'][] = [
                'method' => 'html',
                'selector' => '#test-html',
                'content' => '
                    <strong>
                        HTML replaced successfully
                    </strong>
                ',
            ];

            break;

        case 'text':

            $response['actions'][] = [
                'method' => 'text',
                'selector' => '#test-text',
                'content' => 'Updated text content',
            ];

            break;

        case 'append':

            $response['actions'][] = [
                'method' => 'append',
                'selector' => '#test-append',
                'content' => '
                    <div>
                        New appended item
                    </div>
                ',
            ];

            break;

        case 'prepend':

            $response['actions'][] = [
                'method' => 'prepend',
                'selector' => '#test-append',
                'content' => '
                    <div>
                        Prepended item
                    </div>
                ',
            ];

            break;

        case 'class':

            $response['actions'][] = [
                'method' => 'addClass',
                'selector' => '#test-class',
                'content' => 'red',
            ];

            break;

        case 'removeClass':

            $response['actions'][] = [
                'method' => 'removeClass',
                'selector' => '#test-class',
                'content' => 'red',
            ];

            break;

        case 'hide':

            $response['actions'][] = [
                'method' => 'hide',
                'selector' => '#test-hide',
            ];

            break;

        case 'show':

            $response['actions'][] = [
                'method' => 'show',
                'selector' => '#test-hide',
            ];

            break;

        case 'replace':

            $response['actions'][] = [
                'method' => 'replaceWith',
                'selector' => '#test-replace',
                'content' => '
                    <div id="test-replace">
                        Element replaced
                    </div>
                ',
            ];

            break;

        case 'alert':

            $response['actions'][] = [
                'method' => 'alert',
                'content' => 'Alert works',
            ];

            break;

        case 'reload':

            $response['actions'][] = [
                'method' => 'reload',
            ];

            break;

        case 'location':

            $response['actions'][] = [
                'method' => 'location',
                'content' => 'https://google.com',
            ];

            break;

        case 'script':

            $response['actions'][] = [
                'method' => 'script',
                'content' => '
                    <script>
                        alert("Dynamic script executed");
                    </script>
                ',
            ];

            break;

    }

    echo json_encode($response);

    exit;

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <title>AJAX Test</title>

    <style>

        body {
            font-family: Arial;
            padding: 40px;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }

        .block {
            padding: 20px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }

        .red {
            background: red;
            color: white;
        }

        .js_wait {
            opacity: .5;
            pointer-events: none;
        }

    </style>

</head>
<body>

<h1>AJAX Engine Test</h1>

<div class="buttons">

    <button data-api="/apiResponse.php?api=html">
        HTML
    </button>

    <button data-api="/apiResponse.php?api=text">
        TEXT
    </button>

    <button data-api="/apiResponse.php?api=append">
        APPEND
    </button>

    <button data-api="/apiResponse.php?api=prepend">
        PREPEND
    </button>

    <button data-api="/apiResponse.php?api=class">
        ADD CLASS
    </button>

    <button data-api="/apiResponse.php?api=removeClass">
        REMOVE CLASS
    </button>

    <button data-api="/apiResponse.php?api=hide">
        HIDE
    </button>

    <button data-api="/apiResponse.php?api=show">
        SHOW
    </button>

    <button data-api="/apiResponse.php?api=replace">
        REPLACE
    </button>

    <button data-api="/apiResponse.php?api=alert">
        ALERT
    </button>

    <button data-api="/apiResponse.php?api=reload">
        RELOAD
    </button>

    <button data-api="/apiResponse.php?api=location">
        REDIRECT
    </button>

    <button data-api="/apiResponse.php?api=script">
        SCRIPT
    </button>

</div>

<div
    id="test-html"
    class="block"
>
    Original HTML block
</div>

<div
    id="test-text"
    class="block"
>
    Original text
</div>

<div
    id="test-append"
    class="block"
>
    <div>Initial item</div>
</div>

<div
    id="test-class"
    class="block"
>
    Class test block
</div>

<div
    id="test-hide"
    class="block"
>
    Hide/show block
</div>

<div
    id="test-replace"
    class="block"
>
    Replace block
</div>

<script src="/assets/js/ApiResponse.js"></script>

</body>
</html>
