<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ID Card Print</title>

  <style>
    /* ---- PRINT SETUP (most important part) ---- */
    @page {
      size: A4;
      margin: 0; /* remove default print margins that shift/scale your layout */
    }

    @media print {
      html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
      }

      /* prevent browser "helpful" scaling artifacts */
      * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }

    /* ---- PAGE ---- */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;

      /* A4 page */
      width: 210mm;
      height: 297mm;

      position: relative;
      overflow: hidden; /* don't let anything spill and trigger scaling */
      background: #fff;
    }

    /* ---- CARD ---- */
    .id-card {
      /* Use ONE unit system for printing: mm */
      width: 95mm;     /* fits 2 columns on A4 */
      height: 65mm;    /* fits 4 rows on A4 */
      box-sizing: border-box;

      border: 1px solid #000;
      padding: 4mm;
      background-color: #ffffff;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);

      display: flex;
      flex-direction: column;
      justify-content: space-between;
      align-items: center;

      position: absolute;
    }

    .gym-name {
      font-size: 16pt;
      font-weight: bold;
      color: #2c3e50;
      margin: 0;
      line-height: 1.1;
      text-align: center;
    }

    .user-info {
      font-size: 11pt;
      text-align: center;
      line-height: 1.2;
    }
    .user-info p {
      margin: 1mm 0;
    }

    .qr-code {
      width: 28mm;
      height: 28mm;
      object-fit: contain;
      margin-top: 2mm;
    }

    .valid-until {
      font-size: 9pt;
      margin-top: 2mm;
      text-align: center;
    }

    /* ---- POSITIONS (2 columns x 4 rows) ----
       A4 = 210mm wide. We use:
       left column  = 10mm
       right column = 105mm
       row tops     = 10mm, 80mm, 150mm, 220mm
       (All within page, no overflow => no weird scaling)
    */
    .position-1 { top: 10mm;  left: 10mm; }
    .position-2 { top: 10mm;  left: 105mm; }

    .position-3 { top: 80mm;  left: 10mm; }
    .position-4 { top: 80mm;  left: 105mm; }

    .position-5 { top: 150mm; left: 10mm; }
    .position-6 { top: 150mm; left: 105mm; }

    .position-7 { top: 220mm; left: 10mm; }
    .position-8 { top: 220mm; left: 105mm; }
  </style>
</head>

<body>
  <div class="id-card position-{{ $print->position }}">
    <!-- Gym Name -->
    <div class="gym-name">My Fitness</div>

    <!-- User Information -->
    <div class="user-info">
      <p>Name: {{ $membership->user->getName() }}</p>
      <p>Phone: {{ $membership->user->phone }}</p>
    </div>

    <!-- QR Code -->
    <img
      src="{{ url('qr_codes/' . $membership->qr_code) }}"
      class="qr-code"
      alt="QR Code"
    />

    <!-- Valid Until -->
    <div class="valid-until">
      Valid Until: {{ \Carbon\Carbon::parse($membership->end_date)->format('M d, Y') }}
    </div>
  </div>

  <script>
    window.onload = function () {
      window.print();

      window.onafterprint = function () {
        window.location.href = "{{ route('admin.users.add') }}";
      };
    };
  </script>
</body>
</html>
