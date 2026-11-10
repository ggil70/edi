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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Logs Archivo Pagos</span>
                <hr>
                <div class="card-body">
                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="myTable">
                      <thead>
                        <tr>
                          <th width = "35%">Archivo</th>
                          <th width = "15%">Fecha Proceso</th>
                          <th width = "20%">Nivel Proceso</th>
                          <th width = "25%">Estatus</th>
                          <th width = "5%">Opciones</th>
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
                             $nivel = '';
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
                                  @if($registro->error_archivo == 1)
                                    @php
                                      $estado = 'CULMINADO CON ERROR';
                                    @endphp    
                                  @endif  
                                  @break
                              @case(1)
                                  @php
                                      $estado = 'CON ERRORES';
                                  @endphp    
                                  @break
                          @endswitch


                          @switch($registro->nivel_proceso)
                              @case(1)
                                  @php
                                      $nivel = 'ARCHIVO VALIDADO';
                                  @endphp    
                                  @break
                              @case(2)
                                  @php
                                      $nivel = 'ENCABEZADO';
                                  @endphp    
                                  @break
                              @case(3)
                                  @php
                                      $nivel = 'DETALLE';
                                  @endphp    
                                  @break
                              @case(4)
                                  @php
                                      $nivel = 'PAGO EFECTUADO';
                                  @endphp    
                                  @break

                              @case(5)
                                  @php
                                      $nivel = 'GESTION PAGO ';
                                  @endphp    
                                  @break

                              @case(6)
                                  @php
                                      $nivel = 'BANSTA ';
                                  @endphp    
                                  @break

                          @endswitch


                        <tr>
                          <td><span style="font-size:15;">{{ $registro->nob_archivo }}</span></td>
                          <td><span style="font-size:14;">{{ $registro->fec_proceso }}</span></td>
                          <td><span style="font-size:14;">{{ $nivel }}</span></td>
                          <td><span style="font-size:14;">{{ $estado }}</span></td>

                          <td>
                            <div>
                              @if($registro->status_proceso != 1)
                                  <a href="<?=route('gestion_archivo_detalle',$registro->id)?>"><img src="{{ asset('img/detalle.png') }}" width="40" height="20" alt="Modificar" title = "Detalle" ></a>
                              @endif    
                            </div>
                          </td>
                        </tr>  
                        @endforeach
                        @else
                        <tr>
                          <td >No existen registros</td>
                          <td ></td>
                          <td ></td>
                          <td ></td>
                          <td ></td>

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
  /*  $(document).ready(function() {
        $('#myTable').DataTable({
            responsive: true,
            "language": {
                url: "{{ asset('json/esp.json') }}"
            }
        });
    }); 
  */  
    $(document).ready(function() {
        $('#myTable').DataTable({
            responsive: true,
            "order": [[0, "desc"]],            
            "language": {
                url: "{{ asset('json/esp.json') }}"
            }
        });
    }); 


</script> 
