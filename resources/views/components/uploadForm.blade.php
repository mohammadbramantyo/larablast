<!-- Button to Open Modal -->
<button id="openModalBtn" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">Upload Excel</button>

<!-- Modal Overlay -->
<div id="modalOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden justify-center items-center z-50">
    <!-- Modal Content -->
    <div class="bg-white p-6 rounded-lg max-w-lg mx-auto shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Upload Spatie Simple Excel</h2>
        <form action="{{ route('upload_simple_excel') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="spatie-excel" class="block text-sm font-medium text-gray-700">Choose Excel File</label>
                <input type="file" name="spatie-excel" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">Upload</button>
                <button type="button" id="closeModalBtn" class="bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Toggle Script -->
<script>
    // Get the modal elements
    const modalOverlay = document.getElementById('modalOverlay');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Open the modal when the button is clicked
    openModalBtn.addEventListener('click', () => {
        modalOverlay.classList.remove('hidden');
    });

    // Close the modal when the close button is clicked
    closeModalBtn.addEventListener('click', () => {
        modalOverlay.classList.add('hidden');
    });

    // Close the modal if the user clicks outside the modal content
    window.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            modalOverlay.classList.add('hidden');
        }
    });
</script>