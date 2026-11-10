

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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Subir</span>
                <hr>

                    <main class="card-body">
                        <form action="<?=route('guardarArchivo')?>" method="POST" autocomplete="off" enctype="multipart/form-data">
                        @csrf


                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Archivo</label>
                                <div class="col-md-8">  
                                                            
                                    <input type="file" name="archivo" id="archivo">
                                    <br>
                                    @error('logo')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror                                    
                                </div>
                            </div>
                            <br>

                            <div class="col-md-6 offset-md-2">
                                <button type="submit" class="btn btn-primary">
                                    Subir
                                </button>
                            </div>
                    </main>
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


