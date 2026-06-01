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

    <button data-api="{{ route('test.api.response', 'html') }}">HTML</button>
    <button data-api="{{ route('test.api.response', 'text') }}">TEXT</button>
    <button data-api="{{ route('test.api.response', 'append') }}">APPEND</button>
    <button data-api="{{ route('test.api.response', 'prepend') }}">PREPEND</button>
    <button data-api="{{ route('test.api.response', 'class') }}">ADD CLASS</button>
    <button data-api="{{ route('test.api.response', 'removeClass') }}">REMOVE CLASS</button>
    <button data-api="{{ route('test.api.response', 'hide') }}">HIDE</button>
    <button data-api="{{ route('test.api.response', 'show') }}">SHOW</button>
    <button data-api="{{ route('test.api.response', 'replace') }}">REPLACE</button>
    <button data-api="{{ route('test.api.response', 'alert') }}">ALERT</button>
    <button data-api="{{ route('test.api.response', 'reload') }}">RELOAD</button>
    <button data-api="{{ route('test.api.response', 'location') }}">REDIRECT</button>
    <button data-api="{{ route('test.api.response', 'script') }}">SCRIPT</button>

</div>

<div id="test-html" class="block">
    Original HTML block
</div>

<div id="test-text" class="block">
    Original text
</div>

<div id="test-append" class="block">
    <div>Initial item</div>
</div>

<div id="test-class" class="block">
    Class test block
</div>

<div id="test-hide" class="block">
    Hide/show block
</div>

<div id="test-replace" class="block">
    Replace block
</div>

<script src="{{asset('assets/js/ApiResponse.js')}}"></script>

</body>
</html>
