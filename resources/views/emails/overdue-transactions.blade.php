<h2 style="font-family: Arial, sans-serif; color: #333;">🔔 Transações em Atraso</h2>

<table width="100%" cellpadding="10" cellspacing="0" style="font-family: Arial, sans-serif; border-collapse: collapse;">
    @foreach ($items as $item)
        <tr style="border: 1px solid #e0e0e0; background-color: #f9f9f9;">
            <td style="padding: 16px;">
                <div style="margin-bottom: 8px;">
                    <strong style="color: #555;">Produto:</strong>
                    <span style="color: #000;">{{ $item->transaction->description }}</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #555;">Valor:</strong>
                    <span style="color: #000;">R$ {{ number_format($item->amount, 2, ',', '.') }}</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #555;">Vencimento:</strong>
                    <span style="color: #000;">{{ \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <strong style="color: #555;">Método:</strong>
                    <span style="color: #000;">{{ \App\Helpers\TranslateString::getMethod($item->transaction->method) }}</span>
                </div>
                <div>
                    <strong style="color: #555;">Status:</strong>
                    <span style="color: red;">{{ \App\Helpers\TranslateString::getStatusLabel($item->status) }}</span>
                </div>
            </td>
        </tr>
    @endforeach
</table>
