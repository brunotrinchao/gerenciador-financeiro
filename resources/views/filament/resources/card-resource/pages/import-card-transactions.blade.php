<x-filament::page>
    {{-- Exibe as infos do cartão selecionado --}}
    @if ($selectedCard)
        <div class="mb-6 p-4 bg-gray-100 rounded">
            <h2 class="font-semibold text-lg mb-2">Informações do Cartão Selecionado</h2>
            <p><strong>Nome:</strong> {{ $selectedCard->name }}</p>
            <p><strong>Banco:</strong> {{ $selectedCard->bank->name }}</p>
            <p><strong>Numero:</strong> {{ $selectedCard->number }}</p>
            <p><strong>Bandeira:</strong> {{ $selectedCard->brand->name }}</p>
            <p><strong>Limite:</strong> R$ {{ number_format($selectedCard->limit / 100, 2, ',', '.') }}</p>
        </div>
    @else
        <p class="mb-6 text-sm text-gray-500">Nenhum cartão selecionado.</p>
    @endif

    <p><i>Modelo:</i></p>
    <table>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 p-2">Nome da Movimentação</th>
            <th class="border border-gray-300 p-2">Parcelas Totais</th>
            <th class="border border-gray-300 p-2">Parcelas Pagas</th>
            <th class="border border-gray-300 p-2">Parcelas a Pagar</th>
            <th class="border border-gray-300 p-2">Valor Total</th>
            <th class="border border-gray-300 p-2">Valor da Parcela</th>
            <th class="border border-gray-300 p-2">Mês</th>
            <th class="border border-gray-300 p-2">Ano</th>
        </tr>
    </table>


        <form wire:submit.prevent="parseCsv">
            {{ $this->form }}

            <x-filament::button type="submit" color="primary" class="mt-4">
                Carregar CSV
            </x-filament::button>
        </form>

    @if ($transactions && count($transactions) > 0)
        <form wire:submit.prevent="import" class="mt-6">
            <x-filament::card>
                <p class="mb-2 text-sm text-gray-700 font-semibold">Cartão selecionado:</p>
                <p class="mb-4 text-sm">{{ $selectedCard?->name ?? 'Nenhum cartão selecionado' }}</p>

                <p class="mb-4 text-sm text-gray-600">Revise os dados antes de importar:</p>

                <table class="w-full table-auto border-collapse border border-gray-300">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 p-2">Nome da Movimentação</th>
                        <th class="border border-gray-300 p-2">Parcelas Totais</th>
                        <th class="border border-gray-300 p-2">Parcelas Pagas</th>
                        <th class="border border-gray-300 p-2">Parcelas a Pagar</th>
                        <th class="border border-gray-300 p-2">Valor Total</th>
                        <th class="border border-gray-300 p-2">Valor da Parcela</th>
                        <th class="border border-gray-300 p-2">Mês</th>
                        <th class="border border-gray-300 p-2">Ano</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td class="border border-gray-300 p-2">{{ $transaction['description'] }}</td>
                            <td class="border border-gray-300 p-2 text-center">{{ $transaction['total_installments'] }}</td>
                            <td class="border border-gray-300 p-2 text-center">{{ $transaction['paid_installments'] }}</td>
                            <td class="border border-gray-300 p-2 text-center">{{ $transaction['pending_installments'] ?? '' }}</td>
                            <td class="border border-gray-300 p-2 text-right">R$ {{ number_format($transaction['total_amount'], 2, ',', '.') }}</td>
                            <td class="border border-gray-300 p-2 text-right">R$ {{ number_format($transaction['installment_amount'] ?? 0, 2, ',', '.') }}</td>
                            <td class="border border-gray-300 p-2 text-center">{{ $transaction['month'] }}</td>
                            <td class="border border-gray-300 p-2 text-center">{{ $transaction['year'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <x-filament::button
                    type="submit"
                    color="success"
                    class="mt-4"
                    wire:target="import"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="import">Importar Transações</span>
                    <span wire:loading wire:target="import">
        <svg class="animate-spin h-5 w-5 text-white inline-block" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        Processando...
    </span>
                </x-filament::button>
            </x-filament::card>
        </form>
    @endif
</x-filament::page>
