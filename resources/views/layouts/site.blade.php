<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="theme-color" content="#F2F2F7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">

    <title>{{ config('brauth.title') }}</title>

    <link rel="stylesheet" href="@versioned('css/app.css')">
    <link rel="stylesheet" href="@versioned('css/site.css')">

    <link
    rel="stylesheet"
    href="{{ asset('css/ivao-auth-theme.css') }}"
>

    <style>
        .site__brand.site__brand--mx {
            display: flex;
            justify-content: center;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            height: auto;
            box-sizing: border-box;
            margin: 0 auto 24px;
            padding: 20px 28px;
            border-radius: 18px;
            background: #0d1846;
        }

        .site__brand.site__brand--mx .site__logo--mx {
            display: block;
            width: 260px;
            max-width: 100%;
            height: auto;
            object-fit: contain;
        }

        @media (max-width: 480px) {
            .site__brand.site__brand--mx {
                padding: 16px 20px;
            }

            .site__brand.site__brand--mx .site__logo--mx {
                width: 220px;
            }
        }
    </style>
</head>
<body>
    @include('partials.icons')

    <main class="site">
        <div class="site__brand site__brand--mx">
            <img
                class="site__logo--mx"
                src="{{ asset('assets/imgs/mxivao_logo_white.webp') }}"
                alt="IVAO México"
                width="910"
                height="320"
            >
        </div>

        <div class="card">
            @yield('content')
        </div>

        @hasSection('footer')
            <div class="site__footer">
                @yield('footer')
            </div>
        @endif
    </main>
</body>
</html>