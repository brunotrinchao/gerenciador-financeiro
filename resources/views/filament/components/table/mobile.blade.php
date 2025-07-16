@props(['columns'])

<div>
    @foreach ($getRecord() as $record)
        <div class="p-4 mb-2 rounded shadow bg-white text-sm">
            @foreach ($columns as $column)
                @php
                    $state = $column->getStateUsing?->call($column, $record);
                    $label = $column->getLabel() ?? $column->getName();
                    $formatted = $column->getFormattedState($record);
                @endphp

                <div class="mb-1">
                    <span class="font-semibold text-stone-600">{{ $label }}:</span>
                    <span>{!! $formatted !!}</span>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
