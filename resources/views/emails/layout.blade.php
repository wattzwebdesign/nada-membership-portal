<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NADA')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f5f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333333;
            line-height: 1.6;
        }

        .email-wrapper {
            width: 100%;
            padding: 30px 0;
            background-color: #f4f5f7;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background-color: #374269;
            padding: 30px;
            text-align: center;
        }

        .email-header .logo {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 3px;
            text-decoration: none;
        }

        .email-body {
            padding: 40px 30px;
        }

        .email-body h1 {
            font-size: 22px;
            color: #374269;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .email-body p {
            font-size: 15px;
            color: #555555;
            margin-bottom: 16px;
        }

        .email-body .btn {
            display: inline-block;
            padding: 12px 28px;
            background-color: #374269;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            margin: 16px 0;
        }

        .email-body .btn:hover {
            background-color: #2a3350;
        }

        .email-body .info-box {
            background-color: #f0f2f8;
            border-left: 4px solid #374269;
            padding: 16px 20px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }

        .email-body .info-box p {
            margin: 4px 0;
            font-size: 14px;
        }

        .email-body .info-box strong {
            color: #374269;
        }

        .email-footer {
            background-color: #f9fafb;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e8e8e8;
        }

        .email-footer p {
            font-size: 12px;
            color: #999999;
            margin: 4px 0;
        }

        .email-footer a {
            color: #374269;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            {{-- Header with NADA branding --}}
            <div class="email-header">
                <span class="logo">NADA</span>
            </div>

            {{-- Email content --}}
            <div class="email-body">
                @yield('content')
            </div>

            {{-- Footer --}}
            <div class="email-footer">
                <p><strong>National Acupuncture Detoxification Association</strong></p>
                <p>Providing training and certification in the NADA protocol</p>
                <p>
                    <a href="{{ config('app.url') }}">Visit our website</a> |
                    <a href="mailto:{{ config('mail.from.address', 'info@nadamembership.org') }}">Contact Us</a>
                </p>
                <p>&copy; {{ date('Y') }} NADA. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
