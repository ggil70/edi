<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcesarPagoController;
use App\Http\Controllers\GestionPagoController;
use App\Http\Controllers\CrearArchivoBanstaController;
use App\Http\Controllers\RolUsuarioController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\LimiteProveedorController;
use App\Http\Controllers\AcumuladoProveedorController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\DescargaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ErrorSimfController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioAdmController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\ServicioEdiController;

//portal 



Route::get('/', function () {
    return view('welcome');
});

Route::get('callGestionPago',[GestionPagoController::class, 'callGestionPago'])->name('callGestionPago');
Route::get('valGestionPago' ,[GestionPagoController::class, 'valGestionPago'])->name('valGestionPago');
//Route::get('detalleGestion' ,[GestionPagoController::class, 'valDetalleGestionPago'])->name('detalleGestion');


Route::get('callProcesarArchivos',[ProcesarPagoController::class, 'callProcesarArchivos'])->name('callProcesarArchivos');
Route::get('procesarPago',[ProcesarPagoController::class, 'procesarArchivos'])->name('procesarPago');


//Ruta que permite reprocesar pago
Route::get('callReprocesar',[ProcesarPagoController::class, 'callReprocesar'])->name('callReprocesar');
Route::get('reprocesarPago',[ProcesarPagoController::class, 'reprocesarPago'])->name('reprocesarPago');


Route::get('callCrearBansta/',[CrearArchivoBanstaController::class, 'callCrearBansta'])->name('callCrearBansta');
Route::get('crearArchivoBansta/',[CrearArchivoBanstaController::class, 'crearArchivoBanstas'])->name('crearArchivoBansta');

Route::get('subirArchivo',[ProcesarPagoController::class, 'subirArchivo'])->name('subirArchivo');
Route::post('guardarArchivo',[ProcesarPagoController::class, 'guardarArchivo'])->name('guardarArchivo');



///////////////////////////////////////////////////////////////////////////////////////////////////////
// portal

//////////////////   adm_usuarios   //////////////////////////////////////////////
Route::get('/usuarioAdm_add', [UsuarioAdmController::class,'create'])->name('usuarioAdm_add');
Route::get('/usuarioAdm_delete/{id}', [UsuarioAdmController::class,'usuario_delete'])->name('usuarioAdm_delete');
Route::get('/usuarioAdm_update/{id}', [UsuarioAdmController::class,'edit'])->name('usuarioAdm_update');

Route::post('/save_incluirUsuarioAdm', [UsuarioAdmController::class,'save_create'])->name('save_incluirUsuarioAdm');
Route::post('/save_updateUsuarioAdm', [UsuarioAdmController::class,'save_update'])->name('save_updateUsuarioAdm');
Route::get('/usuarioAdm_delete/{id}', [UsuarioAdmController::class,'usuarioAdm_delete'])->name('usuarioAdm_delete');
Route::post('/delete_usuarioAdm', [UsuarioAdmController::class,'delete'])->name('delete_usuarioAdm');
Route::get('/usuarioAdm_add', [UsuarioAdmController::class,'create'])->name('usuarioAdm_add');
Route::get('/usuarioAdm_index', [UsuarioAdmController::class,'usuarioAdm_index'])->name('usuarioAdm_index');




////////////////// Validar Acceso del Usuario
Route::get('/', [UsuarioController::class,'index'])->name('inicio');
Route::get('/valError/{sw}', [UsuarioController::class,'index_error'])->name('valError');
Route::post('/validar_acceso', [UsuarioController::class,'validar_acceso'])->name('validar_acceso');
Route::get('/olvido_clave', [UsuarioController::class,'olvido_clave'])->name('olvidoClave');
Route::post('/validar_olvido', [UsuarioController::class,'validar_olvido'])->name('validar_olvido');
Route::post('/validar_codigo', [UsuarioController::class,'validar_codigo'])->name('validar_codigo');
Route::post('/cambiar_clave', [UsuarioController::class,'cambiar_clave'])->name('cambiar_clave');



Route::get('/cerrar', [UsuarioController::class,'cerrar_sesion'])->name('cerrar');


//cerrar sesion
/*Route::get('/cerrar', [UsuarioController::class,'cerrar_sesion'])->name('cerrar');
Route::get('/usuario_add', [UsuarioController::class,'create'])->name('usuario_add');
Route::get('/usuario_delete/{id}', [UsuarioController::class,'usuario_delete'])->name('usuario_delete');
Route::get('/usuario_update/{id}', [UsuarioController::class,'edit'])->name('usuario_update');
Route::post('/save_incluirUsuario', [UsuarioController::class,'save_create'])->name('save_incluirUsuario');
Route::post('/save_updateUsuario', [UsuarioController::class,'save_update'])->name('save_updateUsuario');
Route::get('/usuario_delete/{id}', [UsuarioController::class,'usuario_delete'])->name('usuario_delete');
Route::post('/delete_usuario', [UsuarioController::class,'delete'])->name('delete_usuario');
*/


/*Configuracion de parametros y limites de nomina Proveedor*/
Route::get('/parametro_index', [ParametroController::class,'index'])->name('index_parametro');
Route::get('/parametro_update{id}', [ParametroController::class,'edit'])->name('parametro_update');
Route::post('/save_updateParametro', [ParametroController::class,'save_update'])->name('save_updateParametro');

//Limites del proveedor
Route::get('/limite_index', [LimiteProveedorController::class,'index'])->name('index_limite');
Route::get('/limite_update{id}', [LimiteProveedorController::class,'edit'])->name('limite_update');
Route::post('/save_updateLimite', [LimiteProveedorController::class,'save_update'])->name('save_updateLimite');

//limite acumulado proveedor
Route::get('/index_limiteAcumulado', [AcumuladoProveedorController::class,'index'])->name('index_limiteAcumulado');

//Gestion descarga
Route::get('/gestionDescarga', [GestionController::class,'gestionDescarga'])->name('gestionDescarga');

//Gestion archivo
Route::get('/gestionArchivo', [GestionController::class,'gestionArchivo'])->name('gestionArchivo');




Route::get('/gestion_archivo_detalle/{idArchivo}', [GestionController::class,'gestion_archivo_detalle'])->name('gestion_archivo_detalle');




Route::get('/gestion_detalle_pago/{idArchivo}/{nobArchivo}', [GestionController::class,'gestion_detalle_pago'])->name('gestion_detalle_pago');

Route::get('/gestion_encabezado_pago/{idArchivo}/{nobArchivo}', [GestionController::class,'gestion_encabezado_pago'])->name('gestion_encabezado_pago');





/////////////////////////////////////////////////////////////////////////////////////
//////// visualizar archivos para descargar archivos recibidos y bansta
/////////////////////////////////////////////////////////////////////////////////////
//Ver archivo descarga
Route::get('/verArchivo', [DescargaController::class,'verArchivo'])->name('verArchivo');
//mostrar recibido
Route::get('/descargaArchivo/{archivo}', [DescargaController::class,'descargaArchivo'])->name('descargaArchivo');



//Mostrar bansta
Route::get('/descargaBansta/{archivo}', [DescargaController::class,'descargaBansta'])->name('descargaBansta');
//descarga Archivo
Route::get('/downloadArchivo/{archivo}', [DescargaController::class,'downloadArchivo'])->name('downloadArchivo');
//descarga Bansta
Route::get('/downloadBansta/{archivo}', [DescargaController::class,'downloadBansta'])->name('downloadBansta');


//rutas de los clientes
Route::get('/cliente_index', [ClienteController::class,'index'])->name('index_cliente');
Route::get('/cliente_update/{id}', [ClienteController::class,'edit'])->name('cliente_update');
Route::post('/save_updateCliente', [ClienteController::class,'save_update'])->name('save_updateCliente');
Route::get('/cliente_add', [ClienteController::class,'create'])->name('cliente_add');
Route::post('/save_incluirCliente', [ClienteController::class,'save_create'])->name('save_incluirCliente');
Route::get('/cliente_delete/{id}', [ClienteController::class,'cliente_delete'])->name('cliente_delete');
Route::post('/delete_cliente', [ClienteController::class,'delete'])->name('delete_cliente');


//rutas de los Rols
Route::get('/rol_index', [RolUsuarioController::class,'index'])->name('index_rol');
Route::get('/rol_update/{id}', [RolUsuarioController::class,'edit'])->name('rol_update');
Route::post('/save_updateRol', [RolUsuarioController::class,'save_update'])->name('save_updateRol');
Route::get('/rol_delete/{id}', [RolUsuarioController::class,'rol_delete'])->name('rol_delete');
Route::post('/delete_rol', [RolUsuarioController::class,'delete'])->name('delete_rol');
Route::get('/rol_add', [RolUsuarioController::class,'create'])->name('rol_add');
Route::post('/save_incluirRol', [RolUsuarioController::class,'save_create'])->name('save_incluirRol');


//listado de errores
Route::get('/simfError_index', [ErrorSimfController::class,'index'])->name('simfError_index');
Route::get('/simfError_update/{id}', [ErrorSimfController::class,'edit'])->name('simfError_update');
Route::post('/save_updateSimfError', [ErrorSimfController::class,'save_update'])->name('save_updateSimfError');
Route::get('/simfError_delete/{id}', [ErrorSimfController::class,'simfError_delete'])->name('simfError_delete');
Route::post('/delete_simfError', [ErrorSimfController::class,'delete'])->name('delete_simfError');
Route::get('/simfError_add', [ErrorSimfController::class,'create'])->name('simfError_add');
Route::post('/save_incluirSimfError', [ErrorSimfController::class,'save_create'])->name('save_incluirSimfError');



//logs
Route::get('/log_error_archivo', [LogsController::class,'log_error_archivo'])->name('log_error_archivo');
Route::get('/log_error_proceso', [LogsController::class,'log_error_proceso'])->name('log_error_proceso');
Route::get('/log_servicio_edi', [LogsController::class,'log_servicio_edi'])->name('log_servicio_edi');

//servicio edi
Route::get('/servicio_edi', [ServicioEdiController::class,'actServicioEdi'])->name('servicio_edi');

Route::post('/save_detenerEDI', [ServicioEdiController::class,'save_detenerEDI'])->name('save_detenerEDI');

Route::get('/activarEdi', [ServicioEdiController::class,'activarEdi'])->name('activarEdi');

Route::get('/reactivarEdi', [ServicioEdiController::class,'reactivarEdi'])->name('reactivarEdi');


Route::get('/desactivarEdi', [ServicioEdiController::class,'detenerEdi'])->name('desactivarEdi');
