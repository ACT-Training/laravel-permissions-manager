{{-- Role Form Modal --}}
<flux:modal wire:model="showRoleModal" variant="flyout" class="w-full max-w-2xl">
    <form wire:submit="saveRole">
        <flux:heading size="lg" class="mb-6">
            {{ $editingRole ? 'Edit Role' : 'Create Role' }}
        </flux:heading>

        <div class="space-y-6">
            {{-- Name Field --}}
            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input
                    wire:model.live="form.name"
                    placeholder="e.g., Manager"
                    required
                    aria-label="Role name"
                />
                <flux:error name="form.name" />
            </flux:field>

            {{-- Description Field --}}
            <flux:field>
                <flux:label badge="Optional">Description</flux:label>
                <flux:input
                    wire:model.live="form.description"
                    placeholder="Brief description of this role"
                    maxlength="500"
                    aria-label="Role description"
                />
                <flux:error name="form.description" />
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

            {{-- Permissions Field with Category Filter --}}
            <flux:field>
                <div class="mb-4 flex w-full items-center justify-between">
                    <flux:label>Permissions</flux:label>

                    @if (config('permissions-manager.features.category_filtering', true))
                        <flux:dropdown position="bottom" align="end">
                            <flux:button size="sm" variant="ghost" icon-trailing="chevron-down">
                                {{ $categoryFilter ? (collect($this->availableCategories)->firstWhere('value', $categoryFilter)['label'] ?? 'All Permissions') : 'All Permissions' }}
                            </flux:button>

                            <flux:menu class="w-48">
                                <flux:menu.item wire:click="$set('categoryFilter', null)">
                                    All Permissions
                                </flux:menu.item>
                                @foreach ($this->availableCategories as $category)
                                    <flux:menu.item wire:click="$set('categoryFilter', '{{ $category['value'] }}')">
                                        {{ $category['label'] }}
                                    </flux:menu.item>
                                @endforeach
                            </flux:menu>
                        </flux:dropdown>
                    @endif
                </div>

                {{-- Responsive Permission Grid --}}
                <div class="max-h-96 overflow-y-auto rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                    @if ($this->filteredPermissions->count() > 0)
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                            @foreach ($this->filteredPermissions as $permission)
                                <div class="flex items-start">
                                    <flux:checkbox
                                        wire:model.live="form.permissions"
                                        value="{{ $permission->uuid }}"
                                        label="{{ $permission->name }}"
                                        aria-label="Assign {{ $permission->name }} permission"
                                        class="w-full"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($categoryFilter)
                                No permissions found in this category
                            @else
                                No permissions available
                            @endif
                        </p>
                    @endif
                </div>
                <flux:error name="form.permissions" />
                <flux:description>Select which permissions this role should have</flux:description>
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
                <span wire:loading.remove wire:target="saveRole">
                    {{ $editingRole ? 'Save Changes' : 'Create Role' }}
                </span>
                <span wire:loading wire:target="saveRole">Saving...</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
