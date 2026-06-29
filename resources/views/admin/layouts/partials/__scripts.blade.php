    <!-- necessary plugins-->
    <script src="{{ asset('assets/vendors/@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script>
      const header = document.querySelector("header.header");

      document.addEventListener("scroll", () => {
        if (header) {
          header.classList.toggle("shadow-sm", document.documentElement.scrollTop > 0);
        }
      });
    </script>
    <!-- Plugins and scripts required by this view-->
    <script src="{{ asset('assets/vendors/chart.js/js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendors/@coreui/chartjs/js/coreui-chartjs.js') }}"></script>
    <script src="{{ asset('assets/vendors/@coreui/utils/js/index.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.local-time').forEach(function (el) {

                const date = new Date(el.dataset.time);

                const options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                };

                el.textContent = new Intl.DateTimeFormat(undefined, options).format(date);
            });

        });
    </script>


    @stack('auth_scripts')
