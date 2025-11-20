{{-- Permission Form Modal --}}
<flux:modal wire:model="showPermissionModal" variant="flyout" class="w-full max-w-lg">
    <form wire:submit="savePermission">
        <flux:heading size="lg" class="mb-6">
            {{ $editingPermission ? 'Edit Permission' : 'Create Permission' }}
        </flux:heading>

        <div class="space-y-6">
            {{-- Name Field --}}
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input
                    wire:model.live="form.name"
                    placeholder="e.g., manage all meetings"
                    required
                    aria-label="Permission name"
                />
                <flux:error name="form.name" />
                <flux:description>Use lowercase with spaces (e.g., manage all meetings)</flux:description>
            </flux:field>

            {{-- Description Field --}}
            <flux:field>
                <flux:label badge="Optional">Description</flux:label>
                <flux:input
                    wire:model.live="form.description"
                    placeholder="Brief description of what this permission allows"
                    maxlength="500"
                    aria-label="Permission description"
                />
                <flux:error name="form.description" />
            </flux:field>

            {{-- Category Field --}}
            <flux:field>
                <flux:label badge="Optional">Category</flux:label>
                <flux:select
                    wire:model.live="form.category"
                    placeholder="Select a category"
                    variant="listbox"
                    aria-label="Permission category"
                >
                    <flux:select.option value="">None</flux:select.option>
                    @php
                        $categoryEnumClass = config('permissions-manager.category_enum');
                    @endphp
                    @foreach ($categoryEnumClass::toArray() as $key => $value)
                        @php
                            $enum = $categoryEnumClass::make($key);
                        @endphp
                        <flux:select.option value="{{ $key }}">{{ $enum->label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="form.category" />
            </flux:field>

            {{-- Guard Name Field (Conditional) --}}
            @if (config('permissions-manager.guard.show_selection'))
                <flux:field>
                    <flux:label>Guard Name</flux:label>
                    <flux:select
                        wire:model.live="form.guard_name"
                        variant="listbox"
                        aria-label="Guard name"
                    >
                        @foreach (config('permissions-manager.guard.available_guards', ['web']) as $guard)
                            <flux:select.option value="{{ $guard }}">{{ ucfirst($guard) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.guard_name" />
                </flux:field>
            @endif

            {{-- Roles Field --}}
            <flux:field>
                <flux:label>Roles</flux:label>
                <div class="max-h-60 space-y-2 overflow-y-auto rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                    @forelse ($this->availableRoles as $role)
                        <flux:checkbox
                            wire:model.live="form.roles"
                            value="{{ $role->uuid }}"
                            label="{{ $role->name }}"
                            aria-label="Assign to {{ $role->name }} role"
                        />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No roles available</p>
                    @endforelse
                </div>
                <flux:error name="form.roles" />
                <flux:description>Select which roles should have this permission</flux:description>
            </flux:field>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-8 flex justify-end gap-3">
            <flux:modal.close>
                <flux:button variant="ghost" type="button">Cancel</flux:button>
            </flux:modal.close>

            <flux:button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                icon="check"
            >
                <span wire:loading.remove wire:target="savePermission">
                    {{ $editingPermission ? 'Save Changes' : 'Create Permission' }}
                </span>
                <span wire:loading wire:target="savePermission">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
