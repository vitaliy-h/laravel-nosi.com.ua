<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{t('errors.500_title')}}</title>

    <link href="/demo/vendors/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="text-center">

        <div class="display-1 fw-bold text-danger">500</div>

        <h1 class="h3 mb-3">{{t('errors.500_h1')}}</h1>

        <p class="text-muted mb-4">
            {{t('errors.500_text')}}
        </p>

        <div class="d-flex gap-2 justify-content-center">
            <a href="{{ url('/') }}" class="btn btn-primary">
                {{t('buttons.go_home')}}
            </a>
        </div>

    </div>
</div>

</body>
</html>
