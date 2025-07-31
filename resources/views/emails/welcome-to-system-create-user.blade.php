@extends('emails.layout')

@section('title', 'Conta criada com sucesso')

@section('header', 'Bem-vindo ao ' . config('app.name'))

@section('content')
    <p style="color: #555555; font-size: 16px;">
        Olá {{ $user->name }}, você foi cadastrado no sistema com sucesso.
        <br><br>
        Seu login é: <b>{{ $user->email }}</b><br>
        Sua senha temporária é: <b>{{ $password }}</b>
        <br><br>
        Clique no botão abaixo para acessar:
    </p>

    <a href="{{ config('app.url') }}" target="_blank" style="display: inline-block; padding: 12px 24px; margin-top: 20px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
        Acessar {{ config('app.name') }}
    </a>
@endsection
