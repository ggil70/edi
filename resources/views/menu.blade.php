<!-- Menu Principal -->

    @php
        $idRol = Session::get('usuario.idRol');
    @endphp


    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->



        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
              
              <span class="app-brand-text demo menu-text fw-bolder ms-2">
                  <img src="{{ asset('img/activo_logo.png') }}" width="180" height="40" />
              </span>


            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">

            @if($idRol == 1)
            <!-- Layouts -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Administrar Usuarios</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('usuarioAdm_index')?>" class="menu-link">
                    <div data-i18n="Without navbar">Usuarios Banco</div>
                  </a>
                </li>
              </ul>
            </li>
            @endif

            @if($idRol == 1)    
            <!-- Layouts -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Administador Sistema</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('index_rol')?>" class="menu-link">
                    <div data-i18n="Without menu">Roles</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="<?=route('simfError_index')?>" class="menu-link">
                    <div data-i18n="Without menu">Errores Simf</div>
                  </a>
                </li>                
              </ul>
            </li>
            @endif




            @if($idRol == 1 || $idRol == 4)    
            <!-- Layouts -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Configuraci&oacute;n</div>
              </a>

              <ul class="menu-sub">


                <li class="menu-item">
                  <a href="<?=route('index_cliente')?>" class="menu-link">
                    <div data-i18n="Without menu">Clientes</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="<?=route('index_limite')?>" class="menu-link">
                    <div data-i18n="Without menu">L&iacute;mites de Nomina / Proveedor</div>
                  </a>
                </li>

                <li class="menu-item">
                  <a href="<?=route('index_limiteAcumulado')?>" class="menu-link">
                    <div data-i18n="Without menu">Acumulados L&iacute;mites</div>
                  </a>
                </li>                

              </ul>
            </li>
            @endif



            @if($idRol == 1)    
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Procesos de Calcular Pago </div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('callProcesarArchivos')?>" class="menu-link">
                    <div data-i18n="Account">Proceso de Pago</div>
                  </a>
                </li>
              </ul>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('callGestionPago')?>" class="menu-link">
                    <div data-i18n="Account">Proceso Gestión Pago</div>
                  </a>
                </li>
              </ul>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('callReprocesar')?>" class="menu-link">
                    <div data-i18n="Account">Reproceso de Pago Pago</div>
                  </a>
                </li>
              </ul>   

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('callCrearBansta')?>" class="menu-link">
                    <div data-i18n="Account">Generar BANSTA</div>
                  </a>
                </li>
              </ul>     

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('subirArchivo')?>" class="menu-link">
                    <div data-i18n="Account">Subir Archivo</div>
                  </a>
                </li>
              </ul>                                     

            </li>
            @endif


            @if($idRol == 1 || $idRol == 4)  


            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Gestion de Archivos</div>
              </a>
              <ul class="menu-sub"> 
                <li class="menu-item">
                    <a href="<?=route('gestionDescarga')?>" class="menu-link">
                        <div data-i18n="Basic">Descargas</div>
                    </a>
                </li>
              </ul>

              <ul class="menu-sub"> 
                <li class="menu-item">
                    <a href="<?=route('gestionArchivo')?>" class="menu-link">
                        <div data-i18n="Basic">Archivos</div>
                    </a>
                </li>
              </ul>


              <ul class="menu-sub"> 
                <li class="menu-item">
                    <a href="<?=route('verArchivo')?>" class="menu-link">
                        <div data-i18n="Basic">Descargar Archivo</div>
                    </a>
                </li>
              </ul>

            </li>

            @endif


            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-dock-top"></i>
                <div data-i18n="Account Settings">Logs</div>
              </a>


              <ul class="menu-sub"> 
              @if($idRol == 1 || $idRol == 4)                
                <li class="menu-item">
                    <a href="<?=route('log_error_archivo')?>" class="menu-link">
                        <div data-i18n="Basic">Log Error Archivo</div>
                    </a>
                </li>
              @endif  
              @if($idRol == 1 || $idRol == 2 || $idRol == 3)
                <li class="menu-item">
                    <a href="<?=route('log_error_proceso')?>" class="menu-link">
                        <div data-i18n="Basic">Log Error Proceso</div>
                    </a>
                </li>  
              @endif                
              </ul>
              
            </li>





            @if($idRol == 4)    
            <!-- Layouts -->
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Mensaje errores</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('simfError_index')?>" class="menu-link">
                    <div data-i18n="Without menu">Errores Simf</div>
                  </a>
                </li>                
              </ul>
            </li>
            @endif



            @if($idRol == 1 || $idRol == 2 || $idRol == 3 || $idRol == 4)  
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Servicio EDI</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?=route('servicio_edi')?>" class="menu-link">
                    <div data-i18n="Without navbar">Activar/ Detener Servicio</div>
                  </a>
                </li>

                <li class="menu-item">
                    <a href="<?=route('log_servicio_edi')?>" class="menu-link">
                        <div data-i18n="Basic">Log Servicio Edi</div>
                    </a>
                </li>                
              </ul>
            </li>
            @endif




            <hr>

            <li class="menu-item">
                  <a href="<?=route('cerrar')?>" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div data-i18n="Without menu">Culminar sesi&oacute;n</div>
                  </a>

            </li>    
           


          </ul>



            <!--<li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-layout"></i>
                <div data-i18n="Layouts">Cerrar sección</div>
              </a>

              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="<?//=route('cerrar')?>" class="menu-link">
                    <div data-i18n="Without menu">Culminar sección</div>
                  </a>
                </li>
              </ul>
            </li>    
           -->



        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->


          <!-- / Navbar -->
