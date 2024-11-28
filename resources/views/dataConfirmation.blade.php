<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Confirmation</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 text-gray-900 font-sans">

    <div class="max-w-4xl mx-auto my-8 p-6 bg-white rounded-lg shadow-md">
        <!-- Page Title -->
        <h1 class="text-3xl font-semibold text-center text-blue-600 mb-6">Data Upload Summary</h1>

        <!-- Data Summary -->
        <ul class="space-y-4">
            <li class="text-lg">Total Rows Processed: 
                <span class="font-medium text-green-600">{{ $totalRows }}</span>
            </li>
            <li class="text-lg">Duplicate Rows: 
                <span class="font-medium text-red-600">{{ $duplicates }}</span>
            </li>
            <li class="text-lg">Valid Rows: 
                <span class="font-medium text-blue-600">{{ $validRows }}</span>
            </li>
        </ul>

        <!-- Form Section -->
        <form action="{{ route('save.data.option') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <p class="text-lg font-medium text-gray-800">What would you like to do?</p>
            
            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button type="submit" name="action" value="save_valid" class="w-full sm:w-auto bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition">Save Valid Data (Recommended)</button>
                <button type="submit" name="action" value="save_all" class="w-full sm:w-auto bg-yellow-500 text-white py-2 px-4 rounded-md hover:bg-yellow-600 transition">Save All Data (Includes Duplicates)</button>
                <button type="submit" name="action" value="cancel" class="w-full sm:w-auto bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 transition">Cancel</button>
            </div>
        </form>
    </div>

</body>

</html>
