<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; padding: 20px;">

<table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <tr>
        <td align="center" style="padding: 30px 20px;">
            <img src="{{ asset('logo.png') }}" alt="Logo do {{ config('app.name') }}" width="120" style="margin-bottom: 20px;">
            <h2 style="color: #333333;">@yield('header', config('app.name'))</h2>

            @yield('content')
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
