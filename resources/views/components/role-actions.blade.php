@props(['row', 'value' => null, 'column' => null])

@use('ACTTraining\PermissionsManager\Support\Authorization')

@php
    $canUpdate = Authorization::allows('update', $row);
    $canDuplicate = Authorization::allows('create', $row::class);
    $canDelete = ! $row->is_protected
        && Authorization::allows('delete', $row);
@endphp

<div class="flex justify-end">
    @if ($canUpdate || $canDuplicate || $canDelete)
        <flux:dropdown position="left">
            <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" aria-label="Actions for {{ $row->name }} role"></flux:button>

            <flux:menu>
                @if ($canUpdate)
                    <flux:menu.item wire:click="editRole('{{ $row->getKey() }}')" icon="pencil">
                        Edit
                    </flux:menu.item>
                @endif

                @if ($canDuplicate)
                    <flux:menu.item wire:click="duplicateRole('{{ $row->getKey() }}')" icon="document-duplicate">
                        Duplicate
                    </flux:menu.item>
                @endif

                @if ($canDelete)
                    @if ($canUpdate || $canDuplicate)
                        <flux:menu.separator />
                    @endif

                    <flux:menu.item
                        wire:click="deleteRole('{{ $row->getKey() }}')"
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
