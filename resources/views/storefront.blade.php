<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $storefront?->store_name ?? config('app.name') }}</title>
    <style>
        .ss-line { background:#e5e7eb; border-radius:4px; animation:ss-pulse 2s infinite; }
        .ss-block { background:linear-gradient(135deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%); background-size:200% 100%; animation:ss-shimmer 2s infinite; }
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
        <div id="app-shell" class="min-h-screen flex flex-col bg-white">
            <div class="fixed top-0 left-0 right-0 z-[60] h-8 bg-gray-900 flex items-center justify-center">
                <div class="ss-line" style="width:300px;height:10px;border-radius:2px;opacity:0.3"></div>
            </div>
            <div class="pt-8">
                <header class="border-b border-gray-100">
                    <div class="container mx-auto px-4 h-16 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="ss-line" style="width:120px;height:24px"></div>
                            <div class="hidden md:flex items-center gap-6">
                                <div class="ss-line" style="width:60px;height:14px"></div>
                                <div class="ss-line" style="width:70px;height:14px"></div>
                                <div class="ss-line" style="width:50px;height:14px"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="ss-line" style="width:20px;height:20px;border-radius:50%"></div>
                            <div class="ss-line" style="width:20px;height:20px;border-radius:50%"></div>
                        </div>
                    </div>
                </header>
                <main>
                    <div class="w-full h-[80vh] min-h-[500px] max-h-[800px] overflow-hidden relative bg-gray-100">
                        <div class="absolute inset-0 ss-block"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 px-6">
                            <div class="ss-line" style="width:128px;height:16px"></div>
                            <div class="ss-line" style="width:288px;height:40px"></div>
                            <div class="ss-line" style="width:192px;height:20px"></div>
                            <div class="ss-line" style="width:144px;height:48px;border-radius:999px;margin-top:16px"></div>
                        </div>
                    </div>
                    <div class="container mx-auto px-4 py-12">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="space-y-3"> <div class="aspect-square rounded-lg ss-block"></div> <div class="ss-line h-4 w-2/3"></div> </div>
                            <div class="space-y-3"> <div class="aspect-square rounded-lg ss-block"></div> <div class="ss-line h-4 w-2/3"></div> </div>
                            <div class="space-y-3"> <div class="aspect-square rounded-lg ss-block"></div> <div class="ss-line h-4 w-2/3"></div> </div>
                            <div class="space-y-3"> <div class="aspect-square rounded-lg ss-block"></div> <div class="ss-line h-4 w-2/3"></div> </div>
                        </div>
                        <div class="mt-12">
                            <div class="ss-line h-6 w-40 mb-6"></div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                <div class="space-y-3"> <div class="aspect-square ss-block"></div> <div class="ss-line h-4 w-1/2"></div> <div class="ss-line h-5 w-full"></div> <div class="ss-line h-4 w-1/3"></div> </div>
                                <div class="space-y-3"> <div class="aspect-square ss-block"></div> <div class="ss-line h-4 w-1/2"></div> <div class="ss-line h-5 w-full"></div> <div class="ss-line h-4 w-1/3"></div> </div>
                                <div class="space-y-3"> <div class="aspect-square ss-block"></div> <div class="ss-line h-4 w-1/2"></div> <div class="ss-line h-5 w-full"></div> <div class="ss-line h-4 w-1/3"></div> </div>
                                <div class="space-y-3"> <div class="aspect-square ss-block"></div> <div class="ss-line h-4 w-1/2"></div> <div class="ss-line h-5 w-full"></div> <div class="ss-line h-4 w-1/3"></div> </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="bg-gray-900 mt-12">
                    <div class="container mx-auto px-4 py-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="space-y-3"> <div class="ss-line h-6 w-32 bg-gray-700 opacity-40"></div> <div class="ss-line h-4 w-full bg-gray-700 opacity-30"></div> <div class="ss-line h-4 w-3/4 bg-gray-700 opacity-30"></div> </div>
                            <div class="space-y-3"> <div class="ss-line h-6 w-32 bg-gray-700 opacity-40"></div> <div class="ss-line h-4 w-1/2 bg-gray-700 opacity-30"></div> <div class="ss-line h-4 w-1/2 bg-gray-700 opacity-30"></div> </div>
                            <div class="space-y-3"> <div class="ss-line h-6 w-32 bg-gray-700 opacity-40"></div> <div class="ss-line h-4 w-1/2 bg-gray-700 opacity-30"></div> <div class="ss-line h-4 w-1/2 bg-gray-700 opacity-30"></div> </div>
                        </div>
                    </div>
                </footer>
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
            notices: {!! json_encode(isset($storefront->sections_data['notices']) ? $storefront->sections_data['notices'] : null) !!},
        };
    </script>
</body>
</html>