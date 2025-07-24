<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo ao {{ config('app.name') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;">

<table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <tr>
        <td align="center" style="padding: 30px 20px;">
            <img src="{{ asset('storage/logo.png') }}" alt="Logo do {{ config('app.name') }}" width="120" style="margin-bottom: 20px;">
            <h2 style="color: #333333;">Bem-vindo ao {{ config('app.name') }}!</h2>
            <p style="color: #555555; font-size: 16px;">
                Olá {{ $user->name }}, você foi cadastrado no sistema com sucesso. Clique no botão abaixo para acessar:
            </p>

            <a href="{{ config('app.url') }}" target="_blank" style="display: inline-block; padding: 12px 24px; margin-top: 20px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Acessar {{ config('app.name') }}
            </a>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding: 20px; font-size: 12px; color: #999;">
            © {{ date('Y') }} {{ config('app.url') }} — Todos os direitos reservados.
        </td>
    </tr>
</table>

</body>
</html>
