<h2>Transações em Atraso</h2>

<ul>
    @foreach ($items as $item)
        <li>
            <strong>Produto:</strong> {{ $item->transaction->description }}<br>
            <strong>Valor:</strong> R$ {{ number_format($item->amount, 2, ',', '.') }}<br>
            <strong>Vencimento:</strong> {{ \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}<br>
            <strong>Método:</strong> {{ $item->transaction->method }}<br>
            <strong>Status:</strong> {{ $item->status }}
        </li>
    @endforeach
</ul>
