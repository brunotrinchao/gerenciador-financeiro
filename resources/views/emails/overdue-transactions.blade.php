<h2>Transações em Atraso</h2>

<ul style="list-style-type: none; padding: 0;">
    @foreach ($items as $item)
        <li style="padding: 12px; margin-bottom: 16px; border-bottom: 1px solid #ccc;">
            {{ str_replace('<br>', ' | ', $item)  }}
        </li>
    @endforeach
</ul>
