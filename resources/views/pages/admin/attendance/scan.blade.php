@extends('layouts.auth')

@section('title')
    @include('layouts.header', ['title' => 'Attendance | Scan ID'])
@endsection

@section('content')
    <div class="login-box container mt-5">
        <div class="login-logo">
            <a href="/"><b>{{ __('Attendance') }}</b></a>
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <div id="reader" style="width: 100%;"></div>
            </div>
        </div>
    </div>
@endsection



@section('script')
    <script src="{{ asset('assets/qrcode/html5-qrcode.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            const html5QrCode = new Html5Qrcode("reader");

            // Add a beep sound for success
            const beepSound = new Audio("{{ asset('assets/sounds/beep.mp3') }}");

            // QR Code Success Callback
            const onScanSuccess = (decodedText) => {
                // Stop the scanner
                html5QrCode.stop().then(() => {
                    console.log("Scanner stopped.");
                }).catch((err) => {
                    console.error("Failed to stop the scanner:", err);
                });

                // Play beep sound
                beepSound.play();

                // Send the scanned QR code to the server
                $.ajax({
                    url: '{{ route("admin.attendance.record") }}',
                    method: 'POST',
                    data: {
                        qr_code: decodedText,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 5000, // Display for 10 seconds
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload(); // Reload the page after the SweetAlert closes
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                timer: 5000, // Display for 10 seconds
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload(); // Reload the page after the SweetAlert closes
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'An error occurred!',
                            timer: 5000, // Display for 10 seconds
                            showConfirmButton: false,
                        }).then(() => {
                            location.reload(); // Reload the page after the SweetAlert closes
                        });
                    }
                });
            };

            // QR Code Error Callback
            const onScanError = (errorMessage) => {
                console.warn("QR Code scanning error:", errorMessage);
            };

            // Start the QR Code Scanner
            html5QrCode.start(
                { facingMode: "environment" }, // Use rear camera
                {
                    fps: 10,
                    qrbox: { width: 300, height: 300 }, // Larger QR scanning area
                },
                onScanSuccess,
                onScanError
            ).catch((error) => {
                console.error("Error starting QR Code scanner:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to initialize QR Code scanner. Please try another device or browser.',
                });
            });
        });
    </script>
@endsection

