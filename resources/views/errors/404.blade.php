<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - পেজ খুঁজে পাওয়া যায়নি | SocialBoost AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold gradient-text">SocialBoost AI</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center px-4">
        <div class="text-center">
            <div class="mb-8">
                <span class="text-9xl font-bold gradient-text">404</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">পেজ খুঁজে পাওয়া যায়নি</h1>
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                আপনি যে পেজটি খুঁজছেন সেটি বিদ্যমান নেই বা সরানো হয়েছে।
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="gradient-bg text-white px-8 py-3 rounded-full font-medium hover:opacity-90 transition inline-block">
                    হোম পেজে ফিরুন
                </a>
                <a href="/onboarding" class="border-2 border-purple-600 text-purple-600 px-8 py-3 rounded-full font-medium hover:bg-purple-50 transition inline-block">
                    একাউন্ট তৈরি করুন
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400">
            <p>&copy; {{ date('Y') }} SocialBoost AI. সর্বস্বত্ব সংরক্ষিত।</p>
        </div>
    </footer>
</body>
</html>
