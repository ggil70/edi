ee

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
			<div class="wrap-login100" >

				<div class="login100-form validate-form">

					<span class="login100-form-title p-b-43">
						Sistema de Asignación de APIS a Aliados
					</span>

                    <br>

					<span style:"font-size:18px;">La sesi&oacute;n del usuario ha expirado. Si desea continuar presione el boto&oacute;n continuar </span>
            

					<br><br><br><br>

					<div class="container-login100-form-btn">
						<a href="<?=route('inicio')?>" class="btn btn-info">
                             <span style="font-size:20px"><b>Ingresar</b></span>
                        </a>

					</div>

					</div>



					<input type="hidden" id="ruta" name="ruta" value="">
				
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
  
