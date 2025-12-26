<p>Hello {{$invoice->customer->full_name}},</p>
<p>Please find attached your invoice <b>{{ $invoice->invoice_no }}</b> for period <b>{{ $invoice->period }}</b>.</p>
<p>Total: <b>{{ number_format((float)$invoice->total, 2, '.', ' ') }} {{ $invoice->currency }}</b></p>
<p>Payment reference: <b>{{ $invoice->payment_ref }}</b></p>
<p>Thank you.</p>