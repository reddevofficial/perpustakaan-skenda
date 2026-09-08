<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Buku</title>
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #0f172a; font-family: Arial, sans-serif; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .toolbar button { border: 0; border-radius: 8px; background: #0f172a; color: white; cursor: pointer; padding: 10px 16px; }
        .labels { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }
        .label { min-height: 48mm; border: 1px solid #cbd5e1; padding: 4mm; break-inside: avoid; display: flex; flex-direction: column; justify-content: space-between; }
        .library { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .title { margin-top: 5px; font-size: 13px; font-weight: 700; line-height: 1.2; }
        .meta { margin-top: 4px; color: #475569; font-size: 9px; line-height: 1.4; }
        .barcode { display: flex; height: 14mm; align-items: stretch; margin-top: 6px; overflow: hidden; }
        .bar { flex: 0 0 0.7px; background: #fff; }
        .bar.dark { background: #0f172a; }
        .code { margin-top: 2px; font-size: 9px; letter-spacing: 1px; text-align: center; }
        @media print { .print-hidden { display: none !important; } .labels { gap: 3mm; } .label { border-color: #94a3b8; } }
    </style>
</head>
<body>
    <div class="toolbar print-hidden">
        <strong>Label Buku ({{ $copies->count() }})</strong>
        <button type="button" onclick="window.print()">Cetak label</button>
    </div>
    <section class="labels">
        @foreach ($copies as $copy)
            <article class="label">
                <div>
                    <div class="library">Perpustakaan SMK Skenda</div>
                    <div class="title">{{ $copy->book->title }}</div>
                    <div class="meta">ISBN: {{ $copy->book->isbn ?: '-' }}<br>Rak: {{ $copy->shelf_location ?: $copy->book->shelf_location ?: '-' }}</div>
                </div>
                <div>
                    <div class="barcode" aria-label="Barcode {{ $copy->barcode }}">
                        @foreach (str_split($patterns[$copy->id]) as $bit)
                            <span class="bar {{ $bit === '1' ? 'dark' : '' }}"></span>
                        @endforeach
                    </div>
                    <div class="code">{{ $copy->barcode }}</div>
                </div>
            </article>
        @endforeach
    </section>
</body>
</html>
