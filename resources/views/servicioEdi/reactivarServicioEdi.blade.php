<!DOCTYPE html>


@if($estatus_servicio == 1)
    @php
      $activo = "Activado";
    @endphp
@else
    @php
      $activo = "Detenido";
    @endphp
@endif      


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
                <h2 class="card-header">Activar / Detener Servicio EDI</h2>
                <hr>
                <div class="card-body">
                    <div class="form-group row">
                        <h3>Nota: Este m&oacute;dulo permite reactivar  el Servicio EDI. Se recomienda notificar a los usuarios antes de realizar dicha acción.
                        </h3>    
                        <br><br>
                        <h4>El servicio fue detenido por {{ $nob_usuario }}, {{ $ape_usuario }}
                         <br>Fecha / Hora {{ $fec_hora_servicio }} 
                         <br>
                        <b>Motivo: </b> {{ $motivo }}     
                        </h4>    

                        <br><br><br>

                        <div style = "text-align: center; color:red">                       
                            <a href="<?=route('activarEdi')?>" class="btn btn-success">
                                <span style="font-size: 30px;">Activar Servicio</span>
                            </a>
                            &nbsp;&nbsp;
                            <a href="<?=route('servicio_edi')?>" class="btn btn-info">
                                <span style="font-size: 30px;">Cancelar</span>
                            </a>

                        </div>

                        <br><br><br> 

                    </div>    
               </div>
              </div>
              <!--/ Bordered Table -->




          </div>
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
