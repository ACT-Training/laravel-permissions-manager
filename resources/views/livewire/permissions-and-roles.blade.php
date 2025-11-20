<div>
    <div class="space-y-8">
        {{-- Roles Section --}}
        <flux:card class="overflow-hidden">
            <div class="mb-6 flex items-center justify-between">
                <flux:heading size="lg">Roles</flux:heading>
                <flux:button variant="primary" wire:click="$dispatch('create-role')" icon="plus">
                    Create a Role
                </flux:button>
            </div>

            <div class="mx-2 -mb-7">
                <livewire:permissions-manager::roles-table />
            </div>
        </flux:card>

        {{-- Permissions Section --}}
        <flux:card class="overflow-hidden">
            <div class="mb-6 flex items-center justify-between">
                <flux:heading size="lg">Permissions</flux:heading>
                <flux:button variant="primary" wire:click="$dispatch('create-permission')" icon="plus">
                    Create a Permission
                </flux:button>
            </div>

            <div class="mx-2 -mb-7">
                <livewire:permissions-manager::permissions-table />
            </div>
        </flux:card>
    </div>
</div>
