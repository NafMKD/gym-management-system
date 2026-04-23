<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        @include('pages.admin.invoices.partials.document-css')
    </style>
</head>
<body>
@include('pages.admin.invoices.partials.invoice-body', ['invoice' => $invoice, 'forPdf' => true])
</body>
</html>
