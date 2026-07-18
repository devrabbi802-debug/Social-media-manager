<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storefront?->store_name ?? config('app.name') }}</title>

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
        $viteDev = false;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $jsFile = $manifest['resources/storefront/src/main.jsx']['file'] ?? null;
            $cssFile = $manifest['resources/storefront/src/main.jsx']['css'][0] ?? null;
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
    <div id="root"></div>

    <!-- Pass server data to React -->
    <script>
        window.__STOREFRONT_DATA__ = {
            store_name: {!! json_encode($storefront?->store_name ?? config('app.name')) !!},
            store_logo: {!! json_encode($storefront?->store_logo ? Storage::disk('public')->url($storefront->store_logo) : null) !!},
            theme: {!! json_encode($themeConfig) !!},
        };
    </script>
</body>
</html>