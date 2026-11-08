<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Terjadi Kesalahan') - Laundiqo</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <style>
        :root {
            color-scheme: light;
            --sky: #087fb8;
            --sky-dark: #075c91;
            --cyan: #08c6d8;
            --ink: #0b1f3a;
            --muted: #59708b;
            --line: #dbeaf3;
            --soft: #f5faff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #fff 0%, var(--soft) 55%, #e8fbfd 100%);
            color: var(--ink);
            font-family: "Atkinson Hyperlegible", "Nunito Sans", Inter, ui-sans-serif, system-ui, sans-serif;
        }
        .error-shell { display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 2rem 1.25rem; }
        .error-card { width: 100%; max-width: 680px; text-align: center; }
        .brand { display: inline-flex; align-items: center; justify-content: center; margin-bottom: 2.5rem; border-radius: 1rem; background: #fff; padding: .65rem 1rem; box-shadow: 0 10px 30px rgba(7, 92, 145, .08); }
        .brand img { display: block; width: auto; height: 3rem; max-width: 16rem; object-fit: contain; }
        .error-code { margin: 0; color: var(--cyan); font-size: clamp(5rem, 18vw, 9rem); font-weight: 900; letter-spacing: -.08em; line-height: .9; }
        h1 { margin: 1.5rem 0 .75rem; font-size: clamp(1.75rem, 5vw, 2.5rem); line-height: 1.15; }
        .message { margin: 0 auto; max-width: 30rem; color: var(--muted); font-size: 1.05rem; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem; margin-top: 2rem; }
        .button { display: inline-flex; min-height: 2.75rem; align-items: center; justify-content: center; border-radius: .75rem; padding: .75rem 1.25rem; font-size: .9rem; font-weight: 800; text-decoration: none; transition: transform .2s, box-shadow .2s, background .2s; }
        .button:hover { transform: translateY(-2px); }
        .button-primary { background: var(--sky); color: #fff; box-shadow: 0 8px 18px rgba(8, 127, 184, .2); }
        .button-primary:hover { background: var(--sky-dark); }
        .button-secondary { border: 1px solid var(--line); background: #fff; color: var(--ink); }
        .button-secondary:hover { border-color: var(--cyan); box-shadow: 0 8px 18px rgba(7, 92, 145, .08); }
        .hint { margin-top: 2.5rem; color: #7890a6; font-size: .8rem; }
        @media (prefers-reduced-motion: reduce) { .button { transition: none; } .button:hover { transform: none; } }
    </style>
</head>
<body>
    <main class="error-shell">
        <section class="error-card" aria-labelledby="error-title">
            <a class="brand" href="{{ route('marketing.home') }}" aria-label="Laundiqo - Beranda">
                <img src="{{ asset('logo.png') }}" alt="Laundiqo">
            </a>
            <p class="error-code" aria-hidden="true">@yield('code')</p>
            <h1 id="error-title">@yield('heading')</h1>
            <p class="message">@yield('message')</p>
            <div class="actions">
                @auth
                    <a class="button button-primary" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
                @else
                    <a class="button button-primary" href="{{ route('marketing.home') }}">Kembali ke Beranda</a>
                @endauth
                <a class="button button-secondary" href="{{ url()->previous() }}">Halaman Sebelumnya</a>
            </div>
            <p class="hint">Laundiqo &mdash; Kelola Laundry, Lebih Mudah.</p>
        </section>
    </main>
</body>
</html>
