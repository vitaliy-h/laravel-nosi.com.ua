<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background-color:#edf2f7;font-family:Arial,Helvetica,sans-serif;color:#3d4852;">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="background-color:#edf2f7;margin:0;padding:0;width:100%;"
>
    <tr>
        <td align="center" style="padding:40px 0;">

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
                style="max-width:600px;margin:0 auto;"
            >

                {{-- Header --}}
                <tr>
                    <td align="center" style="padding-bottom:25px;">

                        <h1
                            style="
                                margin:0;
                                font-size:28px;
                                font-weight:bold;
                                color:#3d4852;
                            "
                        >
                            {{ config('app.name') }}
                        </h1>

                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td>

                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            role="presentation"
                            style="
                                background-color:#ffffff;
                                border-radius:8px;
                                box-shadow:0 2px 8px rgba(0,0,0,0.05);
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding:40px;
                                        font-size:16px;
                                        line-height:1.5;
                                        color:#3d4852;
                                    "
                                >
                                    {!! $content !!}
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td
                        align="center"
                        style="
                            padding-top:25px;
                            font-size:12px;
                            color:#718096;
                        "
                    >

                        © {{ now()->year }} {{ config('app.name') }}.
                        All rights reserved.

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
