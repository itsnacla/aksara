<!DOCTYPE html>
<html lang="id" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Aksara System' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        [xl-cloak] { display: none !important; }
        
        #reader {
            border: none !important;
            border-radius: 0.75rem;
            overflow: hidden;
            background: #000;
        }
        #reader video {
            object-fit: cover !important;
            border-radius: 0.75rem;
        }
        #reader__dashboard_section_csr button {
            background-color: #005da7 !important;
            color: white !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 1rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            border: none !important;
            margin: 10px 0 !important;
        }
        .dark #reader__dashboard_section_csr button {
            background-color: #3b8fd9 !important;
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-950 dark:text-gray-100 transition-colors duration-300">
    <main>
        {{ $slot }}
    </main>
    @livewireScripts
    @stack('scripts')
</body>
</html>


