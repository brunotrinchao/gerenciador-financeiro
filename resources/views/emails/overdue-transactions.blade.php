@extends('emails.layout')

@section('title', 'Transações em Atraso |' . config('app.name'))

@section('header', 'Transações em Atraso')

@section('content')

<ul style="list-style-type: none; padding: 0;">
    @foreach ($items as $item)
        @php
            $parts = explode(' <br> ', $item);

            $valor = $parts[0] ?? 'Valor não informado';
            $produto = $parts[1] ?? 'Produto não informado';
            $vencimento = $parts[2] ?? 'Data não informada';
            $metodo = $parts[3] ?? 'Método não informado';
            $status = $parts[4] ?? 'Status não informado';
        @endphp

        <div class="item">
            <div><span class="label">Valor:</span> <span class="value">{{ $valor }}</span></div>
            <div><span class="label">Produto:</span> <span class="value">{{ $produto }}</span></div>
            <div><span class="label">Vencimento:</span> <span class="value">{{ $vencimento }}</span></div>
            <div><span class="label">Método:</span> <span class="value">{{ $metodo }}</span></div>
            <div><span class="label">Status:</span> <span class="status">{{ $status }}</span></div>
        </div>
    @endforeach
</ul>
@endsection
