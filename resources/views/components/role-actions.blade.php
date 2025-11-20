@props(['row', 'value' => null, 'column' => null])

<div class="flex justify-end">
    <flux:dropdown position="left">
        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" aria-label="Actions for {{ $row->name }} role"></flux:button>

        <flux:menu>
            <flux:menu.item wire:click="editRole('{{ $row->uuid }}')" icon="pencil">
                Edit
            </flux:menu.item>

            <flux:menu.item wire:click="duplicateRole('{{ $row->uuid }}')" icon="document-duplicate">
                Duplicate
            </flux:menu.item>

            @if (!$row->is_protected)
                <flux:menu.separator />

                <flux:menu.item
                    wire:click="deleteRole('{{ $row->uuid }}')"
                    icon="trash"
                    variant="danger"
                >
                    Delete
                </flux:menu.item>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>
