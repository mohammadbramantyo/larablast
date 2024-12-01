<x-auth>
    <div class="max-w-md mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-5">Forgot Password</h1>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" class="w-full border-gray-300 rounded-lg shadow-sm" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg">Send Password Reset Link</button>
        </form>
    </div>
</x-auth>

