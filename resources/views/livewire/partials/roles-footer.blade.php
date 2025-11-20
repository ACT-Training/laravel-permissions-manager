{{-- Role Form Modal --}}
@include('permissions-manager::livewire.partials.roles-form-modal')

{{-- Delete Confirmation Modal --}}
<flux:modal wire:model="showDeleteModal" class="w-full max-w-md">
    <form wire:submit="confirmDelete">
        <flux:heading size="lg" class="mb-4">Delete Role</flux:heading>

        <div class="mb-6">
            <p class="text-gray-700 dark:text-gray-300">
                Are you sure you want to delete this role? This action cannot be undone.
            </p>
        </div>

        <div class="flex justify-end gap-3">
            <flux:modal.close>
                <flux:button variant="ghost" type="button">Cancel</flux:button>
            </flux:modal.close>

            <flux:button
                type="submit"
                variant="danger"
                wire:loading.attr="disabled"
                icon="trash"
            >
                <span wire:loading.remove wire:target="confirmDelete">Delete</span>
                <span wire:loading wire:target="confirmDelete">Deleting...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
