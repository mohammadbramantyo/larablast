<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body>
    
    <div class="flex h-screen bg-gray-100">
    
       <!-- Include sidebar -->
        @Include('components.sidebar')
    
        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-y-auto">
            <div class="fixed w-full flex items-center justify-between h-16 bg-white border-b border-gray-200">
                <div class="flex items-center px-4">
                    <h1>Database Management</h1>
                </div>
            </div>
            <div class="p-6 mt-16 ml-64">
                @yield('content')
            </div>
        </div>
        
    </div>
</body>
</html>
