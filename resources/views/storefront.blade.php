<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storefront?->store_name ?? config('app.name') }}</title>
    <style>
        .ss-loading { width:100%; height:100%; }
        .ss-banner { width:100%; height:80vh; min-height:500px; max-height:800px; background:#f3f4f6; overflow:hidden; position:relative; }
        .ss-banner-slide { width:100%; height:100%; background:linear-gradient(135deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%); background-size:200% 100%; animation:ss-shimmer 2s infinite; }
        .ss-banner-content { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:16px; padding:0 24px; }
        .ss-line { background:#e5e7eb; border-radius:4px; animation:ss-pulse 2s infinite; }
        @keyframes ss-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        @keyframes ss-pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    </style>

    @if($storefront?->store_favicon)
        <link rel="icon" type="image/x-icon" href="{{ Storage::disk('public')->url($storefront->store_favicon) }}">
    @endif

    <!-- FOWT Prevention: Inject theme vars BEFORE any CSS loads -->
    @if($storefront && $themeConfig)
    <script>
        (function() {
            var config = {!! json_encode($themeConfig) !!};
            if (config.colors) {
                Object.keys(config.colors).forEach(function(k) {
                    document.documentElement.style.setProperty('--color-' + k, config.colors[k]);
                });
            }
            if (config.typography) {
                Object.keys(config.typography).forEach(function(k) {
                    var cssVar = '--' + k.replace(/_/g, '-');
                    document.documentElement.style.setProperty(cssVar, config.typography[k]);
                });
            }
        })();
    </script>
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Storefront Assets (built by Vite) -->
    @php
        $manifestPath = public_path('storefront/.vite/manifest.json');
        $jsFile = null;
        $cssFile = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $entry = $manifest['index.html'] ?? null;
            if ($entry) {
                $jsFile = $entry['file'] ?? null;
                $cssFile = $entry['css'][0] ?? null;
            }
        }
    @endphp
    @if(!empty($jsFile))
        <script type="module" src="{{ asset('storefront/' . $jsFile) }}"></script>
        @if(!empty($cssFile))
            <link rel="stylesheet" href="{{ asset('storefront/' . $cssFile) }}">
        @endif
    @else
        {{-- Fallback: find assets by glob --}}
        @php
            $jsFiles = glob(public_path('storefront/assets/index-*.js'));
            $cssFiles = glob(public_path('storefront/assets/index-*.css'));
            $jsFile = $jsFiles ? basename($jsFiles[0]) : null;
            $cssFile = $cssFiles ? basename($cssFiles[0]) : null;
        @endphp
        @if($jsFile)
            <script type="module" src="{{ asset('storefront/assets/' . $jsFile) }}"></script>
        @endif
        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('storefront/assets/' . $cssFile) }}">
        @endif
    @endif
</head>
<body>
    <div id="root">
        <div id="app-shell">
            <div class="ss-banner">
                <div class="ss-banner-slide"></div>
                <div class="ss-banner-content">
                    <div class="ss-line" style="width:128px;height:16px"></div>
                    <div class="ss-line" style="width:288px;height:40px"></div>
                    <div class="ss-line" style="width:192px;height:20px"></div>
                    <div class="ss-line" style="width:144px;height:48px;border-radius:999px;margin-top:16px"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pass server data to React -->
    <script>
        window.__STOREFRONT_DATA__ = {
            store_name: {!! json_encode($storefront?->store_name ?? config('app.name')) !!},
            store_logo: {!! json_encode($storefront?->store_logo ? Storage::disk('public')->url($storefront->store_logo) : null) !!},
            theme: {!! json_encode($themeConfig) !!},
            theme_slug: {!! json_encode($themeSlug ?? 'clothing-fashion') !!},
        };
    </script>
</body>
</html>