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


    @switch($status_proceso)
        @case(0)
            @php
                $estado = 'PENDIENTE';
            @endphp    
            @break
        @case(2)
            @php
                $estado = 'CULMINADO';
            @endphp    
            @if($error_archivo == 1)
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


    @switch($nivel_proceso)
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


                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">                  <b>DETALLE ARCHIVO RECIBIDO</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{{ route('gestion_encabezado_pago', ['idArchivo' => $id, 'nobArchivo' => $nob_archivo]) }}" class="btn btn-primary">Encabezado Archivo</a>&nbsp;&nbsp;&nbsp;<a href="{{ route('gestion_detalle_pago', ['idArchivo' => $id, 'nobArchivo' => $nob_archivo]) }}" class="btn btn-primary">Detalle Pagos</a>
                </span>
                <hr>
                    <main class="card-body">
    
                    <div class="tabs-container" style="max-width: 800px; width: 100%;">
                        <div class="tab-headers">
                            <div class="tab-header active" onclick="showTab(1)">Informaci&oacute;n b&aacute;sica</div>
                            <div class="tab-header" onclick="showTab(2)">Estado</div>
                            <div class="tab-header" onclick="showTab(3)">Datos del pago</div>
                        </div>
                        <div class="tab-content active" id="content1" >
                                <div class="form-group row">
                                  <label for="name" class="col-md-2 col-form-label text-md-right">Archivo</label>
                                  <div class="col-md-9">
                                      <span>{{$nob_archivo}}</span>
                                  </div>                                  
                                </div>  
                                <div class="form-group row">
                                  <label for="name" class="col-md-2 col-form-label text-md-right">Nro. Orden</label>
                                  <div class="col-md-9">
                                      <span>{{$nro_documento}}</span>
                                  </div>                                  
                                </div>  

                                <div class="form-group row">
                                  <label for="name" class="col-md-2 col-form-label text-md-right">Lote</label>
                                  <div class="col-md-9">
                                      <span>{{$lote}}</span>
                                  </div>                                  
                                </div>  

                                <div class="form-group row">
                                  <label for="name" class="col-md-2 col-form-label text-md-right">Cliente</label>
                                  <div class="col-md-9">
                                      <span>{{ $rif_pagador . " - " . $nob_cliente }}</span>
                                  </div>                                  
                                </div>  

                                <div class="form-group row">
                                  <label for="name" class="col-md-2 col-form-label text-md-right">Fecha Proceso</label>
                                  <div class="col-md-9">
                                      <span>{{ $fec_proceso }}</span>
                                  </div>                                  
                                </div>  

                        </div>
                        <div class="tab-content" id="content2">
                            <div class="form-group row">
                                <label for="name" class="col-md-3 col-form-label text-md-right">Estatus Proceso</label>
                                <div class="col-md-9">
                                    <span>{{$estado}}</span>
                                </div>                                  
                              </div>  
                              <div class="form-group row">
                                <label for="name" class="col-md-3 col-form-label text-md-right">Nivel Proceso</label>
                                <div class="col-md-9">
                                    <span>{{$nivel}}</span>
                                </div>                                  
                              </div>  
                              <div class="form-group row">
                                <label for="name" class="col-md-3 col-form-label text-md-right">Resultado Proceso</label>
                                <div class="col-md-9">
                                    <span>{{ $desc_error }}</span>
                                </div>                                  
                              </div>  

                        </div>
                        <div class="tab-content" id="content3">
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">nro Registro Procesados</label>
                                <div class="col-md-8">
                                    <span>{{$nro_registro}}</span>
                                </div>                                  
                              </div>  
                              <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Total Monto a Pagar</label>
                                <div class="col-md-8">
                                    <span>{{$tot_monto_pago}}</span>
                                </div>                                  
                              </div>  
                              <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Nro. Registro Pagados</label>
                                <div class="col-md-8">
                                    <span>{{ $nro_registro_pagado }}</span>
                                </div>                                  
                              </div>  

                              <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Nro. Registro Rechazados</label>
                                <div class="col-md-4">
                                    <span>{{ $nro_registro_rechazo + $nro_detalle_rec }}</span>
                                </div>                                  
                              </div>  

                              <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">Total Pagado</label>
                                <div class="col-md-8">
                                    <span>{{ $tot_mto_pago_final }}</span>
                                </div>                                  
                              </div>  


                        </div>
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
