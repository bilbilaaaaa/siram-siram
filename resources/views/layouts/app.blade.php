<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f1f8f4">

        <title>@yield('title', 'SiramSiram')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh">
        <div class="min-h-dvh lg:flex">
            @include('partials.sidebar')

            <main @class(['min-w-0 flex-1 lg:ml-64 lg:pb-10', 'pb-28' => ! View::hasSection('hide-bottom-nav')])>
                <div class="mx-auto w-full max-w-5xl px-6 pb-8 pt-6 sm:px-8 lg:px-10 lg:pt-10">
                    @yield('content')
                </div>
            </main>
        </div>

        @unless (View::hasSection('hide-bottom-nav'))
            @include('partials.bottom-nav')
        @endunless
    </body>
</html>
