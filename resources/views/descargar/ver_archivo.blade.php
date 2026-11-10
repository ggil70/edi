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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Logs Descarga Pagos</span>
                <hr>
                <div class="card-body">
                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="myTable">
                      <thead>
                        <tr>
                          <th width = "30%">Archivo</th>
                          <th width = "10%">Fecha archivo</th>
                          <th width = "20%">Cliente</th>
                          <th width = "10%">Estatus</th>
                          <th width = "10%">Recibido</th>
                          <th width = "20%">Bansta</th>
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
                             $status = '';
                             $estado = '';
                          @endphp
                          

                          @switch($registro->status_proceso)
                              @case(0)
                                  @php
                                      $estado = 'PENDIENTE';
                                  @endphp    
                                  @break
                              @case(2)
                                  @php
                                      $estado = 'CULMINADO';
                                  @endphp    
                                  @break
                              @case(1)
                                  @php
                                      $estado = 'CON ERRORES';
                                  @endphp    
                                  @break
                          @endswitch

                          
                          @if($registro->nivel_proceso == 6)
                            @php
                                $recibido = 'DESCARGA';
                                $bansta = 'DESCARGA';
                            @endphp

                          @else
                            @php
                                $recibido = 'DESCARGA';
                                $bansta = '';
                            @endphp
                          @endif

                        <tr>
                          <td><span class="badge bg-label-primary me-1">{{ $registro->nob_archivo }}</span></td>
                          <td><span class="badge bg-label-primary me-1">{{ $registro->fec_proceso }}</span></td>                          
                          <td><span class="badge bg-label-primary me-1">{{ $registro->nob_cliente }}</span></td>
                          <td><span class="badge bg-label-primary me-1">{{ $estado }}</span></td>
                          <td><span>
                                <a href="{{ route('descargaArchivo', $registro->nob_archivo) }}" class="btn btn-primary">Descargar</a>
                              </span>  
                          </td>

                          <td><span>
                              @if($registro->nivel_proceso == 6)
                                  <a href="{{ route('descargaBansta', $registro->archivo_bansta) }}" class="btn btn-primary">Descargar</a>
                              @endif
                            </span>
                          </td>
                        </tr>  
                        @endforeach
                        @else
                        <tr>
                          <td>No existen registros</td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
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

</script> 
