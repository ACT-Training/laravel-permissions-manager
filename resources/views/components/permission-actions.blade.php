@props([
    'value',
    'column',
    'row',
])

@use('ACTTraining\PermissionsManager\Support\Authorization')

@php
    $canUpdate = Authorization::allows('update', $row);
    $canDelete = Authorization::allows('delete', $row);
@endphp

<div class="flex justify-end">
    @if ($canUpdate || $canDelete)
        <flux:dropdown position="left">
            <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" aria-label="Actions for {{ $row->name }}"></flux:button>

            <flux:menu>
                @if ($canUpdate)
                    <flux:menu.item wire:click="editPermission('{{ $row->getKey() }}')" icon="pencil">
                        Edit
                    </flux:menu.item>
                @endif

                @if ($canUpdate && $canDelete)
                    <flux:menu.separator />
                @endif

                @if ($canDelete)
                    <flux:menu.item
                        wire:click="deletePermission('{{ $row->getKey() }}')"
                        icon="trash"
                        variant="danger"
                    >
                        Delete
                    </flux:menu.item>
                @endif
            </flux:menu>
        </flux:dropdown>
    @endif
</div>
