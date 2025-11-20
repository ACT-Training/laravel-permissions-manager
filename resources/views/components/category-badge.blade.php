@props(['row', 'value' => null, 'column' => null])

@if($row->category)
    @php
        $categoryEnum = config('permissions-manager.category_enum');
        $category = $categoryEnum::make($row->category);
    @endphp
    <flux:badge color="{{ $category->color() }}">
        {{ $category->label }}
    </flux:badge>
@else
    <flux:badge color="zinc" class="text-gray-500">
        Not Set
    </flux:badge>
@endif
