@extends('emails.layout')

@section('title', 'Transações que vencem hoje |' . config('app.name'))

@section('header', 'Transações que vencem hoje')

@section('content')
    @foreach ($items as $item)
        <div class="item" style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
            <div><strong>Valor:</strong> R$ {{ $item['amount'] }}</div>
            <div><strong>Produto:</strong> {{ $item['description'] }}</div>
            <div><strong>Vencimento:</strong> {{ $item['due_date'] }}</div>
            <div><strong>Método:</strong> {{ $item['method'] }}</div>
            <div><strong>Status:</strong> {{ $item['status'] }}</div>
        </div>
    @endforeach
@endsection

