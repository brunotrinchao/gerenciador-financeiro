<div class="space-y-4">
    @forelse ($logs as $log)
        <div class="p-4 bg-gray-100 rounded shadow">
            <p class="text-sm text-gray-600">{{ $log->created_at->format('d/m/Y H:i') }}</p>
            <p class="text-gray-600">{{ $log->description }}</p>

            @if($log->old_values || $log->new_values)
                <div class="mt-2 text-xs text-gray-700">
                    <strong>Antes:</strong>
                    <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    <strong>Depois:</strong>
                    <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500">Nenhum log encontrado.</p>
    @endforelse
</div>
