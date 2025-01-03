<!-- Jquery -->
<script src="{{ asset('assets/jquery/jquery.min.js') }}"></script>
<!-- Jquery UI -->
<script src="{{ asset('assets/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap -->
<script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Data Tables -->
<script src="{{ asset('assets/datatables/jquery.dataTables.min.js') }}"></script>
<!-- Overlay Scrollbars -->
<script src="{{ asset('assets/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('assets/select2/js/select2.full.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- Moment JS -->
<script src="{{ asset('assets/moment/moment.min.js') }}"></script>
<!-- Chart JS -->
<script src="{{ asset('assets/chart.js/Chart.min.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('assets/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Admin LTE -->
<script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
<script>
    let Toast;
    $(function () {
        // Make the widgets sortable Using jquery UI
        $('.connectedSortable').sortable({
            placeholder: 'sort-highlight',
            connectWith: '.connectedSortable',
            handle: '.card-header, .nav-tabs',
            forcePlaceholderSize: true,
            zIndex: 999999
        })

        //Initialize toaster
        Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        // show authorization errors
        @if (session(\App\Http\Controllers\Controller::AUTH_ERROR_))
            Toast.fire({
                icon: 'error',
                title: {!! "'" . session(\App\Http\Controllers\Controller::AUTH_ERROR_) . "'" !!}
            });
        @endif

        // show page errors
        @if (session('response-page-not-found'))
        Toast.fire({
            icon: 'error',
            title: {!! "'" . session('response-page-not-found') . "'" !!}
        });
        @endif

        $('.loading-button').on('click', function () {
            const $button = $(this);
            $button.prop('disabled', true);
            $button.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            $button.closest('form').submit();
        });

    });

</script>
