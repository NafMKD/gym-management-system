<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            position: relative; /* Use relative positioning for the body */
            height: 297mm; /* A4 height */
            width: 210mm; /* A4 width */
        }

        .id-card {
            height: 265px;
            width: 430px;
            border: 1px solid #000;
            padding: 10px;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            position: absolute; /* Use absolute positioning for ID cards */
        }

        /* Define positions for each card based on $print->position */
        .position-1 {
            top: 20px;
            left: 20px;
        }

        .position-2 {
            top: 20px;
            left: 520px; /* Move to the right column */
        }

        .position-3 {
            top: 325px; /* Height of one card */
            left: 20px;
        }

        .position-4 {
            top: 325px;
            left: 520px;
        }

        .position-5 {
            top: 630px; /* Height of two cards */
            left: 20px;
        }

        .position-6 {
            top: 630px;
            left: 520px;
        }

        .position-7 {
            top: 935px; /* Height of three cards */
            left: 20px;
        }

        .position-8 {
            top: 935px;
            left: 520px;
        }

        .gym-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .user-info {
            font-size: 15px;
            text-align: center;
        }

        .user-info p {
            margin: 2px 0;
        }

        .qr-code {
            width: 100px;
            height: 100px;
            margin-top: 10px;
        }

        .valid-until {
            font-size: 12px;
            margin-top: 10px;
        }
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
        <img src="{{ url('qr_codes/' . $membership->qr_code) }}" class="qr-code" alt="QR Code">

        <!-- Valid Until -->
        <div class="valid-until">
            Valid Until: {{ \Carbon\Carbon::parse($membership->end_date)->format('M d, Y') }}
        </div>
    </div>

    <script>
        window.onload = function() {
            // Trigger the print dialog
            window.print();

            // Redirect back to the dashboard after printing
            window.onafterprint = function() {
                window.location.href = "{{ route('admin.users.add') }}";
            };
        };
    </script>
</body>
</html>
