<!-- Trigger Button for Delete -->
<form id="deleteForm" action="{{ route('clear_data') }}" method="POST" style="display: none;">
    @csrf
</form>

<button id="deleteButton" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete All Data</button>

<!-- Delete Confirmation Modal Overlay -->
<div id="deleteModalOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex justify-center items-center z-50">
    <!-- Delete Confirmation Modal Content -->
    <div id="deleteModalContent" class="bg-white p-6 rounded-lg max-w-lg shadow-lg transform scale-95 opacity-0 transition-all duration-300">


        <div class="flex justify-center mb-4 p-4">
            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="64px" height="64px" viewBox="0 0 50 50" enable-background="new 0 0 50 50" xml:space="preserve" fill="#000000">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <line fill="none" stroke="#ff0000" stroke-linecap="round" stroke-linejoin="round" x1="35.253" y1="14.747" x2="14.747" y2="35.254"></line>
                    <circle fill="none" stroke="#ff0000" stroke-linejoin="round" cx="25" cy="25" r="23.668"></circle>
                    <line fill="none" stroke="#ff0000" stroke-linecap="round" stroke-linejoin="round" x1="35.253" y1="35.254" x2="14.747" y2="14.747"></line>
                </g>
            </svg>
        </div>


        <h2 class="text-xl font-semibold mb-4">Are you sure you want to delete all data?</h2>
        <p class="mb-4">This action cannot be undone.</p>
        <div class="flex gap-4">
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
            <button type="button" id="cancelDelete" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal Toggle Script for Delete Confirmation -->
<script>
    // Get the modal elements for Delete Confirmation
    const deleteModalOverlay = document.getElementById('deleteModalOverlay');
    const deleteModalContent = document.getElementById('deleteModalContent');
    const deleteButton = document.getElementById('deleteButton');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    // Open the delete confirmation modal when the delete button is clicked
    deleteButton.addEventListener('click', () => {
        deleteModalOverlay.classList.remove('hidden');
        setTimeout(() => {
            deleteModalContent.classList.remove('scale-95', 'opacity-0');
            deleteModalContent.classList.add('scale-100', 'opacity-100');
        }, 10); // Small delay to trigger transition
    });

    // Close the modal when the cancel button is clicked
    cancelDelete.addEventListener('click', closeDeleteModal);

    // Close the modal if the user clicks outside the modal content
    deleteModalOverlay.addEventListener('click', (event) => {
        if (event.target === deleteModalOverlay) {
            closeDeleteModal();
        }
    });

    function closeDeleteModal() {
        deleteModalContent.classList.remove('scale-100', 'opacity-100');
        deleteModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            deleteModalOverlay.classList.add('hidden');
        }, 300); // Wait for the animation to complete
    }

    // When the user clicks confirm, submit the form and close the modal
    confirmDelete.addEventListener('click', () => {
        document.getElementById('deleteForm').submit(); // Submit the form to delete data
        closeDeleteModal(); // Close the modal
    });
</script>