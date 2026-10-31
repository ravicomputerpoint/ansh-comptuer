</main>
      <!--end::App Main-->
      <!--begin::Footer-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Ansh Computer</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2026-2027&nbsp;
          <a href="https://rcpoint.in" class="text-decoration-none">Ravi Computer Point</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->
    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>

    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="<?= BASE_URL ?>js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->
    <script defer>
        new DataTable('#table',{
          pageLength: 5,
          lengthMenu: [5, 10, 15, 20]
        });
    </script>
    

   
   
   
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
