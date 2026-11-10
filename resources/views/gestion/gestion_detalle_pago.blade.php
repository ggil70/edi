<!DOCTYPE html>

<!--=========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>



<!-- Head -->
@include('head')
<!-- no head -->

<style>

.tab-content { display: none; }
.tab-content.active { display: block; }

</style>    



  <body>

  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">  

      <!-- menu -->
      @include('menu')
      <!-- Fin Menu -->   

      <!-- Encabezado -->
      @include('encabezado')
      <!-- Fin Encabezado --> 
    <div class="container-xxl flex-grow-1 container-p-y">
   
        <div >    
              <!-- Bordered Table -->
              <div class="card">


                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">                  <b>DETALLE ARCHIVO RECIBIDO</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{ route('gestion_detalle_pago', $id, $ob_archivo) }}" class="btn btn-primary">Detalle Pagos</a>
                </span>
                <hr>
                    <main class="card-body">
    
                    <div class="tabs-container">
                        <div class="tab-headers">
                            <div class="tab-header active" onclick="showTab(1)">Informaci&oacute;n b&aacute;sica</div>
                            <div class="tab-header" onclick="showTab(2)">Estado</div>
                            <div class="tab-header" onclick="showTab(3)">Datos del pago</div>
                        </div>
                        <div class="tab-content active" id="content1">Contenido de la Pestaña Uno</div>
                        <div class="tab-content" id="content2">Contenido de la Pestaña Dos</div>
                        <div class="tab-content" id="content3">Contenido de la Pestaña Tres</div>
                    </div>

                    </main>

                </div>
              </div>
              <!--/ Bordered Table -->




          </div>




      <!-- Layout container -->
      <div class="layout-page">

          

      <!---------------------------- Inicio FOOTEeeeeR ------------------------------------------------------>
         @include('footer')
      <!-------------------------     FIN  FOOTER   ----------------------------------------------------->


      <div class="content-backdrop fade"></div>
    </div>
          <!-- Content wrapper -->
  </div>
        <!-- / Layout page -->
  </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


  </body>
</html>


<script src="{{ asset('js/sweetAlert/sweetalert2.all.js')}}"></script>

<script>
    function showTab(tabIndex) {
        // Ocultar todo el contenido
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.querySelectorAll('.tab-header').forEach(header => header.classList.remove('active'));

        // Mostrar el contenido y la cabecera seleccionados
        document.getElementById('content' + tabIndex).classList.add('active');
        document.querySelector('.tab-header:nth-child(' + tabIndex + ')').classList.add('active');
    }

           
</script>  
