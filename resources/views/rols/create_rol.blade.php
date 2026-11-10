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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Agregar rol de usuario</span>
                <hr>

                    <main class="card-body">
                        <form action="<?=route('save_incluirRol')?>" method="POST" autocomplete="off">
                        @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Nombre Rol</label>
                                <div class="col-md-7">
                                    <input type="text" id="nob_rol" class="form-control" name="nob_rol" maxlength="50" value="{{ old('nob_rol') }}" autofocus >
                                    @error('nob_rol')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    @if($swIncluir==1)
                                      <div style="color: red">*El rol editado ya existe</div>
                                    @endif                                     


                                </div>
                            </div>
                            <br>

                            <div class="form-group row">
                                <label for="estatus" class="col-md-2 col-form-label text-md-right">Estatus</label>

                                <div class="col-md-7">
                                    
                                   <input class="form-check-input" type="checkbox" name="estatus" value="1" 
                                          id="estatus" checked/>
                                   <label class="col-form-label" for="estatus"> ACTIVO </label>
                                </div>


                            </div>
                            <br>


                            <div class="col-md-6 offset-md-2">
                                <button type="submit" class="btn btn-primary">
                                    Guardar
                                </button>
                                <a href="<?=route('index_rol')?>" class="btn btn-info">
                                    Cancelar?
                                </a>
                            </div>
                    </form>        
                    </main>
                    
                </div>
              </div>
              <!--/ Bordered Table -->




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



/*    function sweetAlert(tipo, titulo, config, footer, time) {
        Swal.fire({
            position: 'top-center',
            type: tipo,
            title: titulo,
            footer: footer,
            showConfirmButton: false,
            timer: time

        })
    }


            sweetAlert("success", "Debe completar el campo 'Otro de la tercera (3) pregunta'", true, "Si el problema persiste, contacte con el soporte de Minmujer", 5000);
*/            
</script>  
