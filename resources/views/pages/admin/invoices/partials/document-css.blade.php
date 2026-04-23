.invoice-document {
    color: #212529;
    background: #ffffff;
    border: 1px solid #d9dee3;
    padding: 24px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

.invoice-document * {
    box-sizing: border-box;
}

.invoice-document .text-right {
    text-align: right;
}

.invoice-document .text-muted {
    color: #6c757d !important;
}

.invoice-document table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-head {
    margin-bottom: 24px;
}

.invoice-head td {
    vertical-align: middle;
}

.invoice-brand {
    display: inline-block;
    white-space: nowrap;
}

.invoice-logo {
    width: 30px;
    height: 30px;
    margin-right: 10px;
    border-radius: 50%;
    object-fit: cover;
    vertical-align: middle;
}

.invoice-brand-name {
    display: inline-block;
    font-size: 30px;
    font-weight: 700;
    line-height: 1;
    vertical-align: middle;
}

.invoice-date {
    text-align: right;
    color: #6c757d;
    font-size: 14px;
}

.invoice-label {
    display: block;
    margin-bottom: 8px;
    color: #6c757d;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.invoice-info {
    margin-bottom: 24px;
    table-layout: fixed;
}

.invoice-info td {
    width: 33.3333%;
    padding-right: 18px;
    vertical-align: top;
}

.invoice-info td:last-child {
    padding-right: 0;
}

.invoice-address {
    margin: 0;
    font-style: normal;
    line-height: 1.65;
}

.invoice-detail-line {
    margin: 0 0 8px;
    line-height: 1.6;
}

.invoice-items {
    margin-bottom: 24px;
}

.invoice-items th,
.invoice-items td {
    padding: 10px 12px;
    border: 1px solid #d9dee3;
    vertical-align: top;
}

.invoice-items thead th {
    background: #f4f6f9;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.invoice-footer {
    table-layout: fixed;
}

.invoice-footer td {
    width: 50%;
    vertical-align: top;
}

.invoice-summary-wrap {
    padding-left: 28px;
}

.invoice-lead {
    margin: 0 0 12px;
    font-size: 18px;
    font-weight: 400;
}

.invoice-summary-title {
    margin: 0 0 10px;
    font-size: 18px;
    font-weight: 400;
    text-align: right;
}

.invoice-summary {
    margin-left: auto;
    max-width: 320px;
}

.invoice-summary th,
.invoice-summary td {
    padding: 8px 0;
    border-top: 1px solid #d9dee3;
}

.invoice-summary tr:first-child th,
.invoice-summary tr:first-child td {
    border-top: 0;
}

.invoice-summary th {
    width: 60%;
    text-align: left;
    font-weight: 700;
}

.invoice-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.2;
    vertical-align: middle;
}

.invoice-status-badge.is-paid {
    color: #155724;
    background: #d4edda;
    border-color: #badbcc;
}

.invoice-status-badge.is-unpaid {
    color: #856404;
    background: #fff3cd;
    border-color: #ffe69c;
}

.invoice-money {
    white-space: nowrap;
}

@page {
    size: A4;
    margin: 12mm;
}

@media screen {
    .invoice-document {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
}

@media print {
    body {
        margin: 0 !important;
        background: #ffffff !important;
    }

    .main-sidebar,
    .main-header,
    .content-header,
    .main-footer,
    .no-print {
        display: none !important;
    }

    .content-wrapper,
    .content,
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 0 !important;
        background: #ffffff !important;
    }

    .invoice-document {
        border: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
    }
}
