@php
    $modalId = 'delete-modal-' . uniqid();
@endphp


<!-- Trigger Button -->
<button type="button" 
        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded"
        onclick="document.getElementById('{{ $modalId }}').classList.remove('hidden')">
    {{ $slot ?? 'Delete' }}
</button>

<!-- Modal -->
<div id="{{ $modalId }}" 
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white rounded shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Confirm Deletion</h2>
        <p class="mb-6">Are you sure you want to delete this?</p>
        <div class="flex justify-end gap-4">
            <button type="button" 
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                    onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')">
                Cancel
            </button>
            <form action="{{ $action }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Confirm
                </button>
            </form>
        </div>
    </div>
</div>