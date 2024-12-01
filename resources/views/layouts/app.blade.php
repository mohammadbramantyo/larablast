<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMNILeads | @yield('title', 'Default Title')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @vite('resources/css/app.css')
</head>

<body>

    <div class="flex h-screen bg-gray-100">

        <!-- Include sidebar -->
        @Include('components.sidebar')

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-y-auto">

            <div class="p-4 ml-64">
                <x-header :title="view()->yieldContent('title', 'Default Title')"/>
                @yield('content')
            </div>
        </div>

    </div>
    @stack('scripts')
</body>

</html>