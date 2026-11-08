<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach (['marketing.home', 'marketing.about', 'marketing.contact', 'marketing.privacy', 'marketing.terms', 'login', 'register', 'tracking.index'] as $routeName)
        <url><loc>{{ route($routeName) }}</loc></url>
    @endforeach
</urlset>
