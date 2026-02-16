<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NADA Certificate — {{ $certificate->user->full_name }}</title>
    <style>
        @page { size: landscape; margin: 0; }
        body { margin: 0; padding: 0; width: 100%; height: 100%; font-family: 'Times New Roman', serif; }

        .left-ribbon { position: absolute; top: 0; left: 0; width: 360px; z-index: -10; }
        .right-ribbon { position: absolute; bottom: 0; right: 0; width: 240px; z-index: -10; }

        .header h1 {
            font-size: 58px; font-weight: 400; color: #313131;
            text-align: center; letter-spacing: 1.5px;
            padding-top: 8%; line-height: 49px; margin: 0;
            word-break: break-all;
        }

        .sub-header h3 {
            text-align: center; color: #374269;
            letter-spacing: 1.5px; padding-top: 15px; margin: 0;
        }

        .recipient-name { text-align: center; height: 165px; position: relative; }
        .recipient-name h1 {
            margin: 0; position: relative; top: 30px; font-size: 42px;
        }
        .recipient-name img {
            z-index: -10; position: absolute;
            left: 50%; transform: translateX(-50%); bottom: 0;
        }

        .description { position: relative; top: -50px; }
        .description p {
            text-align: center; color: #374269;
            margin: 5px 0; font-size: 24px;
        }

        .signatures { position: relative; width: 100%; top: -40px; }
        .signatures img { width: 310px; }
        .signatures img:first-child { float: left; margin-left: 10%; margin-top: 4px; }
        .signatures img:last-child { float: right; margin-right: 10%; }

        .cert-meta { position: absolute; bottom: 20px; left: 20px; font-size: 22px; }
        .cert-meta p { margin: 15px 0; color: #d39c27; }
        .cert-meta p span { color: #000; }

        .footer {
            position: absolute; width: 100%; bottom: 40px;
            text-align: center; display: block; left: 0;
        }
        .footer img { width: 260px; margin: auto; }
    </style>
</head>
@php
    $img = fn($file) => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path("images/certificates/{$file}")));
@endphp
<body>
    <img class="left-ribbon" src="{{ $img('left-ribbon.png') }}" />
    <img class="right-ribbon" src="{{ $img('right-ribbon.png') }}" />

    <div class="header">
        <h1>NATIONAL ACUPUNCTURE<br>DETOXIFICATION ASSOCIATION</h1>
    </div>
    <div class="sub-header">
        <h3>THE FOLLOWING CERTIFICATE IS<br>GIVEN TO</h3>
    </div>
    <div class="recipient-name">
        <h1>{{ $certificate->user->full_name }}</h1>
        <img src="{{ $img('middle-line.png') }}" />
    </div>
    <div class="description">
        <p>has successfully completed all training and satisfied competencies for all</p>
        <p>Acupuncture Detox Specialist with the National Acupuncture</p>
        <p>Detoxification Association</p>
    </div>
    <div class="signatures">
        <img src="{{ $img('president.png') }}" />
        <img src="{{ $img('vice-president.png') }}" />
    </div>
    <div class="footer">
        <img src="{{ $img('nada-logo.png') }}" />
    </div>
    <div class="cert-meta">
        <p>Date Issued: <span>{{ $certificate->date_issued->format('F j, Y') }}</span></p>
        <p>NADA ID# <span>{{ $certificate->certificate_code }}</span></p>
        @if($certificate->expiration_date)
        <p>Expiration Date: <span>{{ $certificate->expiration_date->format('F j, Y') }}</span></p>
        @endif
    </div>
</body>
</html>
