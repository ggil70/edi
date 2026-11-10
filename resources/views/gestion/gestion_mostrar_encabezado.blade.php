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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Encabezado del archivo  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{ route('gestion_archivo_detalle', $idArchivo) }}" class="btn btn-primary">Volver</a></span>
                <hr>
                <div style="text-align: left; margin-right: 1.8em;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Nombre del Archivo: {{ $nobArchivo }} </b>
                </div>

                <div class="card-body">
                        <div class="form-group row">
                          <label for="name" class="col-md-2 col-form-label text-md-right">Nombre Archivo</label>
                          <div class="col-md-10">
                              <span>{{$nobArchivo}}</span>
                          </div>                                  
                        </div>  

                        <div class="form-group row">
                          <label for="name" class="col-md-2 col-form-label text-md-right">Respuesta</label>
                          <div class="col-md-10">
                              <span>{{$desc_error_encabezado}}</span>
                          </div>                                  
                        </div>                          

                        <div class="form-group row">
                          <label for="name" class="col-md-2 col-form-label text-md-right">Encabezado</label>
                          <div class="col-md-10">
                              <span>{{$linea}}</span>
                          </div>                                  
                        </div>  



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
    $(document).ready(function() {
        $('#myTable').DataTable({
            responsive: true,
            "language": {
                url: "{{ asset('json/esp.json') }}"
            }
        });
    }); 

    function sweetAlert(tipo, titulo, config, footer, time) {
        Swal.fire({
            position: 'top-center',
            type: tipo,
            title: titulo,
            footer: footer,
            showConfirmButton: false,
            timer: time

        })
    }


</script> 
