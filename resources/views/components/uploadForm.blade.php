<!-- Button to Open Modal -->
<button id="openModalBtn" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">Upload Excel</button>

<!-- Modal Overlay -->
<div id="modalOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center z-50">
    <!-- Modal Content -->
    <div id="modalContent" class="bg-white p-6 rounded-lg max-w-lg shadow-lg transform scale-95 opacity-0 transition-all duration-300">
        <h2 class="text-xl font-semibold mb-4">Upload Excel or CSV</h2>
        <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="excel_file" class="block text-sm font-medium text-gray-700">Choose Excel File</label>
                <input type="file" name="excel_file" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
    const modalContent = document.getElementById('modalContent');
    const openModalBtn = document.getElementById('openModalBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // Open the modal when the button is clicked
    openModalBtn.addEventListener('click', () => {
        modalOverlay.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10); // Small delay to trigger transition
    });

    // Close the modal when the close button is clicked
    closeModalBtn.addEventListener('click', closeModal);

    // Close the modal if the user clicks outside the modal content
    modalOverlay.addEventListener('click', (event) => {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    function closeModal() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalOverlay.classList.add('hidden');
        }, 300); // Wait for the animation to complete
    }
</script>
