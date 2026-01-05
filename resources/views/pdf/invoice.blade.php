<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; width: 100%; }

    .cols { width: 100%; border-collapse: collapse; padding: 5px;}
    .cols td { vertical-align: top; padding: 0; }
    .gap16 td:first-child { padding-right: 16px; }
    .gap12 td:first-child { padding-right: 12px; }

    .box { border:1px solid #ddd; padding:10px; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th,td { border:1px solid #ddd; padding:8px; }
    th { background:#f5f5f5; }
    .right { text-align:right; }
    .muted { color:#555; font-size:10px; }
  </style>
</head>
<body>

<!-- HEADER: 2 columns -->
<table class="cols gap16">
  <tr>
    <td style="width:60%; padding:10px">
      <h2 style="margin:0 0 6px">INVOICE</h2>
      <div><b>No:</b> {{ $invoice->invoice_no }}</div>
      <div><b>Period:</b> {{ $invoice->period }}</div>
      <div><b>Currency:</b> {{ $invoice->currency }}</div>
      <div><b>Status:</b> {{ $invoice->status }}</div>
      <div><b>Issued at:</b> {{ $invoice->issued_at ?? '—' }}</div>
      <div><b>Due date:</b> {{ $invoice->due_date ?? '—' }}</div>
      <div class="muted" style="margin-top:6px"><b>Payment reference:</b> {{ $paymentRef }}</div>
    </td>

    <td style="width:40%; padding:10px" class="right">
      <div><b>Pay via QR</b></div>
      <img src="{{ $qrBase64 }}" style="width:140px; height:140px" alt="QR">
      <div class="muted" style="margin-top:6px">Include reference: {{ $paymentRef }}</div>
    </td>
  </tr>
</table>

<!-- BILLED TO / BENEFICIARY: 2 columns -->
<table class="cols gap12" style="margin-top:12px">
  <tr>
    <td style="width:50%; padding:10px">
      <div class="">
        <div><b>Billed to</b></div>
        <div>{{ $invoice->customer->full_name ?? '—' }}</div>
        <div>{{ $invoice->customer->phone ?? '—' }}</div>
        <div>{{ $invoice->customer->house_address ?? '—' }}, apt {{ $invoice->customer->apartment ?? '—' }}</div>
      </div>
    </td>

    <td style="width:50%; padding:10px">
      <div class="">
        <div><b>Beneficiary</b></div>
        <div>{{ $billing->company_name ?? '—' }}</div>
        <div>{{ $billing->company_id ?? '—' }}</div>
        <div>{{ $billing->address ?? '—' }}</div>
        <div>{{ $billing->email ?? '—' }} / {{ $billing->phone ?? '—' }}</div>
      </div>
    </td>
  </tr>
</table>

<table>
  <thead>
    <tr>
      <th>Service</th>
      <th class="right">Amount</th>
    </tr>
  </thead>
  <tbody>
    @foreach(($invoice->items ?? collect()) as $item)
      <tr>
        <td>{{ ucfirst($item->service) ?? '—' }}</td>
        <td class="right">{{ number_format((float)$item->amount, 2, '.', ' ') }} {{ $invoice->currency }}</td>
      </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <th class="right">Total</th>
      <th class="right">{{ number_format((float)$invoice->total, 2, '.', ' ') }} {{ $invoice->currency }}</th>
    </tr>
  </tfoot>
</table>

<div class="box" style="margin-top:12px">
  <div><b>Bank details</b></div>
  <div><b>IBAN:</b> {{ $billing->iban ?? '—' }}</div>
  <div><b>Bank:</b> {{ $billing->bank_name ?? '—' }}</div>
  <div><b>SWIFT:</b> {{ $billing->swift ?? '—' }}</div>
  <div class="muted" style="margin-top:6px">
    Please include <b>{{ $paymentRef }}</b> in payment details.
  </div>
</div>

</body>
</html>
