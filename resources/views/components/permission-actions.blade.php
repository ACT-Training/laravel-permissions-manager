@props([
    'value',
    'column',
    'row',
])

<div class="flex justify-end">
    <flux:dropdown position="left">
        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" aria-label="Actions for {{ $row->name }}"></flux:button>

        <flux:menu>
            <flux:menu.item wire:click="editPermission('{{ $row->uuid }}')" icon="pencil">
                Edit
            </flux:menu.item>

            <flux:menu.separator />

            <flux:menu.item
                wire:click="deletePermission('{{ $row->uuid }}')"
                icon="trash"
                variant="danger"
            >
                Delete
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
