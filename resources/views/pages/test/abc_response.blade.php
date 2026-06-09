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

    <button data-abcr="{{ route('test.abc.response', 'html') }}">HTML</button>
    <button data-abcr="{{ route('test.abc.response', 'text') }}">TEXT</button>
    <button data-abcr="{{ route('test.abc.response', 'append') }}">APPEND</button>
    <button data-abcr="{{ route('test.abc.response', 'prepend') }}">PREPEND</button>
    <button data-abcr="{{ route('test.abc.response', 'class') }}">ADD CLASS</button>
    <button data-abcr="{{ route('test.abc.response', 'removeClass') }}">REMOVE CLASS</button>
    <button data-abcr="{{ route('test.abc.response', 'hide') }}">HIDE</button>
    <button data-abcr="{{ route('test.abc.response', 'show') }}">SHOW</button>
    <button data-abcr="{{ route('test.abc.response', 'replace') }}">REPLACE</button>
    <button data-abcr="{{ route('test.abc.response', 'alert') }}">ALERT</button>
    <button data-abcr="{{ route('test.abc.response', 'reload') }}">RELOAD</button>
    <button data-abcr="{{ route('test.abc.response', 'location') }}">REDIRECT</button>
    <button data-abcr="{{ route('test.abc.response', 'script') }}">SCRIPT</button>

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

<form
    id="test-form"
    data-abcr="{{ route('test.abc.response', 'form') }}"
    method="POST"
    class="block"
>
    <form
        id="test-form"
        data-abcr="{{ route('test.abc.response', 'form') }}"
        method="POST"
        class="block p-4 border rounded"
    >
        <div class="abcr-result mb-3"></div>

        @csrf

        <div class="mb-3">

            <input
                type="text"
                name="name"
                placeholder="Your name"
                class="form-control"
            >

        </div>

        <div class="mb-3">

            <input
                type="email"
                name="email"
                placeholder="Your email"
                class="form-control"
            >

        </div>

        <div class="mb-3">

    <textarea
        name="message"
        placeholder="Message"
        class="form-control"
        rows="5"
    ></textarea>

        </div>

        <button
            type="submit"
            class="btn btn-primary"
        >
            SEND FORM
        </button>

    </form>


</form>

<script src="{{asset('assets/js/abc-response.js')}}"></script>

</body>
</html>
