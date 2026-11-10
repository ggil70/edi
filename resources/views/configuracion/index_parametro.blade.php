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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Par&aacute;metros</span>
                <hr>
                <div class="card-body">
                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="myTable">
                      <thead>
                        <tr>
                          <th width = "20%">Codigo</th>
                          <th width = "50%">Descrpci&oacute;n</th>
                          <th width = "20%">Valor</th>
                          <th width = "10%">Opciones</th>
                        </tr>
                      </thead>
                      <tbody>

                        @csrf
                        @if($swRegistro == 1)
                        @php
                            $nro = 0;
                            $activo = "";
                        @endphp                        
                        
                        @foreach($records as $registro)   
                          @php
                          $nro = $nro + 1;
                          @endphp
                        <tr>
                          <td>{{ $registro->cod_parametro }}</td>
                          <td><span class="badge bg-label-primary me-1">{{ $registro->desc_parametro }}</span></td>
                          <td><span class="badge bg-label-primary me-1">{{ $registro->valor }}</span></td>


                          <td>
                            <div>
                              <a href="<?=route('parametro_update',$registro->id)?>"><img src="{{ asset('img/modificar.png') }}" width="20" height="15" alt="Modificar" title = "Modificar" ></a>
                            </div>
                          </td>
                        @endforeach
                        

                        </tr>
                        @else
                        <tr>
                          <td colspan = "4">No existen registros</td>
                        </tr>  
                        @endif


                      </tbody>
                    </table>
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

       @if($swOperacion == 1)
           sweetAlert("success", "El registro fue ingresado satisfactoriamente'", true, "", 2000);
       @endif

       @if($swOperacion == 2)
           sweetAlert("success", "El registro fue modificado satisfactoriamente'", true, "", 2000);
       @endif

       @if($swOperacion == 3)
           sweetAlert("success", "El registro fue Inhabilitado satisfactoriamente'", true, "", 2000);
       @endif        

</script> 
