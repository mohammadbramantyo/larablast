<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMNILeads | {{ $title ?? 'Authentication' }}</title>
    @vite('resources/css/app.css') <!-- Include Tailwind CSS -->
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 via-slate-200 to-gray-300 flex items-center justify-center">
    <div class="w-full max-w-lg p-8 bg-white shadow-lg rounded-lg">
        {{ $slot }}
    </div>
</body>
</html>
