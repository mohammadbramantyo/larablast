<x-auth>
    <div class="max-w-md mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-5">Register</h1>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium">Username</label>
                <input id="username" name="username" type="text" class="w-full border-gray-300 rounded-lg shadow-sm p-2" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" class="w-full border-gray-300 rounded-lg shadow-sm p-2" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" class="w-full border-gray-300 rounded-lg shadow-sm p-2" required>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border-gray-300 rounded-lg shadow-sm p-2" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg">Register</button>
        </form>
    </div>
</x-auth>

