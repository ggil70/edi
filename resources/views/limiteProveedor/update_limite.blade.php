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
      
  
      <!-- Fin Configura el checked del campo activo -->  


    <div class="container-xxl flex-grow-1 container-p-y">
   
        <div >    
              <!-- Bordered Table -->
              <div class="card">
                <h5 class="card-header">Modificar L&iacute;mites De Pago</h5>
                <hr>
                
                <div class="card-body">
                        <form action="<?=route('save_updateLimite')?>" method="POST" enctype="multipart/form-data">
                        @csrf
                            <br>
                            <div class="form-group row">
                                <label for="cliente" class="col-md-2 col-form-label text-md-right">Cliente</label>
                                <div class="col-md-6">
                                    <input type="cliente" class="form-control" name="cliente" value = "{{ $nob_cliente }}" readonly="true" >
            

                                </div>
                            </div>
                            <br>
                            <div class="form-group row">
                                <label for="mto_nomina" class="col-md-2 col-form-label text-md-right">Monto L&iacute;mite Nomina</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" step="0.01" id="mto_nomina" name="mto_nomina" value = "{{ $lim_max_nomina }}" onchange="formatearDecimales('mto_nomina')" >
                                    @error('mto_nomina')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror                                     

                                </div>
                            </div>

                            <br>
                            <div class="form-group row">
                                <label for="mto_proveedor" class="col-md-2 col-form-label text-md-right">Monto L&iacute;mite Proveedor</label>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" step="0.01" id="mto_proveedor" name="mto_proveedor" value = "{{ $lim_max_proveedor }}" onchange="formatearDecimales('mto_proveedor')" >
                                    @error('mto_proveedor')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror                                     

                                </div>
                            </div>
                            <br><br>


                            <div class="col-md-6 offset-md-2">
                                <button type="submit" class="btn btn-primary">
                                    Modificar
                                </button>
                                <a href="<?=route('index_limite')?>" class="btn btn-info">
                                    Cancelar?
                                </a>
                            </div>

                            <input type="hidden" id="id" name="id" value = "{{ $id }}">
                    </div>
                    </form>
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


<script>


function formatearDecimales(tipoPago) {
  let numero = document.getElementById(tipoPago).value;
  document.getElementById(tipoPago).value = parseFloat(numero).toFixed(2);
}


</script>

