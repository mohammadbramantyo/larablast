<div class="shadow-sm pb-4">
    <div class="px-4 py-3 flex justify-between items-start">
        <!-- Logo or App Name -->
        <div class="text-lg font-bold flex">
            <a href="{{ route('dashboard') }}">OMNILeads</a>
            <h1 class=" text-base text-slate-600 pl-4">
                {{ $title }}
            </h1>
        </div>

        <!-- Dropdown -->
        <div class="relative">
            <!-- User Name Button -->
            <button 
                class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none"
                onclick="toggleDropdown()"
                id="dropdownButton"
            >
                <span>{{ Auth::user()->username }}</span>
                <svg class="ml-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div 
                id="dropdownMenu" 
                class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-md hidden z-10"
            >
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    window.addEventListener('click', function (e) {
        const dropdownMenu = document.getElementById('dropdownMenu');
        const dropdownButton = document.getElementById('dropdownButton');
        if (!dropdownButton.contains(e.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });
</script>
