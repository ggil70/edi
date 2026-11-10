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
                <span style="margin-top:0.6em; margin-left: 0.6em; font-size:20px">Agregar Cliente</span>
                <hr>

                    <main class="card-body">
                        <form action="<?=route('save_incluirCliente')?>" method="POST" autocomplete="off">
                        @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Nombre cliente</label>
                                <div class="col-md-9">
                                    <input type="text" id="nob_cliente" class="form-control" name="nob_cliente" maxlength="100" value="{{ old('nob_cliente') }}" autofocus >
                                    @error('nob_cliente')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    @if($swIncluir==1)
                                      <div style="color: red">*El Cliente editado ya existe</div>
                                    @endif                                      


                                </div>
                            </div>
                            <br>

                            <div class="form-group row">
                                <label for="tipo_doc" class="col-md-2 col-form-label text-md-right">Rif</label>
                                <div class="col-md-1">

                                <select id="tipo_doc" name="tipo_doc" class="form-select" style="width:70px;">
                                      @foreach($tipoDocumento as $indice => $elemento)
                                          <option value="{{$indice}}">{{$indice}}</option>
                                      @endforeach
                                </select>
                                </div>
                                <div class="col-md-6">
                                <input type="text" id="doc_cliente" class="form-control" name="doc_cliente" maxlength="15" style="width:200px;" onkeyup="valDocumento()" value="{{ old('doc_cliente') }}">
                                    @error('doc_cliente')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <br>
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Codigo EDI</label>
                                <div class="col-md-9">
                                    <input type="text" id="codEdi" class="form-control" name="codEdi" maxlength="100" value="{{ old('codEdi') }}" autofocus >
                                    @error('codEdi')
                                        <span style="color:red;">* 
                                            {{ $message }}
                                        </span>
                                    @enderror

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
                                <a href="<?=route('index_cliente')?>" class="btn btn-info">
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
