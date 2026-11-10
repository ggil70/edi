

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


<body style="background-color: #666666;">	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">

				 <form class="login100-form validate-form" id="formulario" autocomplete="off" action="<?=route('validar_acceso')?>" method="POST">

				 	@csrf 
					<span class="login100-form-title p-b-43">
						Sistema de Pago Nomina y Proveedores EDI
					</span>

                    
                    @if($swValidar == 0)
                       @php 
                          $display = 'none'  
                       @endphp
                    @else
                       @php 
                          $display = 'inline'
                       @endphp
                    @endif   


					<div class='row'>
	     				<div id="error_validar" style="display:{{$display}}; " class=" col-md-12 alert alert-danger">
	     					@if(isset($mensaje))
	     					    {{$mensaje}}
	     					@endif    
	     				</div>
					</div>

					<div style="border: 1px solid blue; padding: 5px; ">

                    <div>
						<spam style="color: blue; font-weight: bold;">Login</spam>
						<br>
						<input type="text" name="usuario" id="usuario" style=" padding: 5px;" width="500px" class=" col-md-12" focus>
                            @error('usuario')
                                <span style="color:red;">* 
                                    {{ $message }}
                                </span>
                            @enderror

  					</div>		
					<br>
					<div>
						<spam style="color: blue; font-weight: bold;">Password</spam>
						<br>
						<input type="password" name="pass" id="pass" style="padding: 5px;" class="col-md-11" width="50px" >

                        <img src="{{ asset('img/abrirOjo.png') }}" width="25" height="25" id="imagenOjo" onClick = "activarPassword();">

						    <br>
                            @error('pass')
                                <span style="color:red;">* 
                                    {{ $message }}
                                </span>
                            @enderror

					</div>
					<br>


					<div class="container-login100-form-btn">
						<button type="submit" class="login100-form-btn">
							Acceder
						</button>
					</div>


					<div class="container-login100-form-btn">
						<a href="<?=route('olvidoClave');?>">Olvido su Contraseña ?</a> 
					</div>



					</div>



					<input type="hidden" id="ruta" name="ruta" value="">
				
				</form>

				<div class="login100-more" 
				     style="background-image: url('{{ asset('img/login.jpg') }}')">
				</div>
			</div>
		</div>
	</div>
</html>	
	
	
    <script>

    document.getElementById("usuario").focus();

    //variable para la manipulación de la imagen abrir ojo
    var swOjo = 0;

    function activarPassword(){
    	var inputPassword = document.getElementById("pass");
    	var contenido = inputPassword.value;
    	if(contenido.length > 0){
	        if(swOjo == 0){
	        	inputPassword.type = "text";
	            swOjo = 1;
	            document.getElementById('imagenOjo').src = "{{ asset('img/cerrarOjo.png') }}";
	        }else{
	        	inputPassword.type = "password";
	        	swOjo = 0;
	        	document.getElementById('imagenOjo').src = "{{ asset('img/abrirOjo.png') }}";
	        } 
	    }    
    }


    function validar(){
		error=0;
		formulario = "formulario";
		objeto = "usuario";
		campo = document.forms[formulario].elements[objeto].value;
		campo = campo.replace(/(^\s*)|(\s*$)/g,"");
		usuario = campo.replace(/(^\s*)|(\s*$)/g,"");
		if (campo.length==0){
		    error = 1;
		}
		objeto = "pass";
		campo = document.forms[formulario].elements[objeto].value;
		campo = campo.replace(/(^\s*)|(\s*$)/g,"");
		clave = campo.replace(/(^\s*)|(\s*$)/g,"");
		if (campo.length==0){
		    error = 1;
		}
		
		if(error == 1){
            document.getElementById('error_validar').style.display = "inline";
            document.getElementById('error_validar').innerHTML = "Acceso denegado. Verifique el P-Cero y el Password ";
        }    

    }    
 
    </script>
  
