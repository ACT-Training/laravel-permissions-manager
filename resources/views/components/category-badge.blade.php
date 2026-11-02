@props(['row', 'value' => null, 'column' => null])

@php
    $categoryLabel = \ACTTraining\PermissionsManager\Support\PermissionCategories::label($row->category);
@endphp

@if($categoryLabel !== null)
    <flux:badge color="{{ \ACTTraining\PermissionsManager\Support\PermissionCategories::color($row->category) ?? 'zinc' }}">
        {{ $categoryLabel }}
    </flux:badge>
@elseif($row->category)
    <flux:badge color="zinc">
        {{ $row->category }}
    </flux:badge>
@else
    <flux:badge color="zinc" class="text-gray-500">
        Not Set
    </flux:badge>
@endif
