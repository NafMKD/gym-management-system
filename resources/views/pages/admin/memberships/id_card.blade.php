<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .id-card {
            height: 6cm;
            width: 9cm;
            border: 1px solid #000;
            padding: 10px;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .gym-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .user-info {
            font-size: 12px;
            text-align: center;
        }

        .user-info p {
            margin: 2px 0;
        }

        .qr-code {
            width: 70px;
            height: 70px;
            margin-top: 10px;
        }

        .valid-until {
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="id-card">
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
                window.location.href = "{{ route('admin.memberships.add') }}";
            };
        };
    </script>
</body>
</html>
