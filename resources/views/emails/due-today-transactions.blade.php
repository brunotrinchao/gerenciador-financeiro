<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Transações que vencem hoje</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #1f2937;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        h2 {
            color: #111827;
            font-size: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 8px;
        }

        .item {
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 6px;
            background-color: #f9fafb;
        }

        .label {
            font-weight: bold;
            color: #374151;
        }

        .value {
            color: #111827;
        }

        .status {
            font-weight: bold;
            color: #f59e0b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Transações que vencem hoje</h2>

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
</div>
</body>
</html>

