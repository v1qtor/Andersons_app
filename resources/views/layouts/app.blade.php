<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite('resources/css/app.css')
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .checkpoint-label {
            background: transparent !important;
            border: none !important;
        }
        .leaflet-container {
            height: 100%;
            width: 100%;
        }
        
        /* Force sidebar to stay on left */
        body {
            display: flex;
        }
        body > aside,
        body > div > aside {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" style="display: flex;">
    <aside class="w-64 flex-shrink-0 bg-white border-r border-gray-200" style="min-height: 100vh;">
        <x-layouts.app.sidebar />
    </aside>
    <main class="flex-1" style="min-width: 0;">
        @yield('content')
    </main>
</body>
</html>