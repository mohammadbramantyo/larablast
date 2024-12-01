<x-auth>
    <x-slot name="title">Login</x-slot>
    <div class="max-w-md mx-auto">
        <h1 class="text-3xl font-extrabold text-center mb-6 text-gray-700">Login</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
                    required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-2"
                    required>
            </div>
            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                Login
            </button>
        </form>
    </div>
</x-auth>