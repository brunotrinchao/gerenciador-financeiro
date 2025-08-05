@extends('emails.layout')

@section('title', 'Transações que vencem hoje |' . config('app.name'))

@section('header', 'Transações que vencem hoje')

@section('content')
    @foreach ($items as $item)
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
            <h3 style="margin-top: 0;">{{ $item['description'] }}</h3>
            <div style="margin-bottom: 6px;"><strong>Valor:</strong> R$ {{ $item['amount'] }}</div>
            <div style="margin-bottom: 6px;"><strong>Vencimento:</strong> {{ $item['due_date'] }}</div>
            <div style="margin-bottom: 6px;"><strong>Método:</strong> {{ $item['method'] }}</div>
            <div style="margin-bottom: 6px;"><strong>Parcela:</strong> {{ $item['installment'] }}</div>
            <div><strong>Status:</strong> {{ $item['status'] }}</div>
        </div>
    @endforeach
@endsection
