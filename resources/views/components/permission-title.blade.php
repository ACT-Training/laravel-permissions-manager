@props(['row', 'value' => null, 'column' => null])

<div class="flex w-full items-start gap-2">
    <div class="mt-1 h-3 w-3 flex-shrink-0 rounded-full bg-orange-500" aria-hidden="true"></div>
    <div class="flex flex-col gap-1">
        <div class="text-gray-900 dark:text-gray-100">
            {{ $value ?? $row->name }}
        </div>
        @if($row->description)
            <div class="text-sm leading-snug text-gray-500 dark:text-gray-400">
                {{ $row->description }}
            </div>
        @endif
    </div>
</div>
