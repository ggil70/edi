<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


use Exception;

trait FuncValidarCampos
{
    /**
     * Realiza operaciones con archivos físicos
     *
     * @param array $data Los datos del pago a validar.
     * @return \Illuminate\Http\JsonResponse
     */

    //Permite validar los campos según reglas del negocio

    function valTipoDocumento($tipoDocumento)
    {
        $td = strtoupper($tipoDocumento);
        if ($td == 'J' || $td == 'G' || $td == 'C' || $td == 'V' || $td == 'E' || $td == 'P') {
            return 0;
        } else {
            return 1;
        }
    }


    function valDigitos($digitos)
    {
        if (is_numeric($digitos)) {
            return 0;
        } else {
            return 1;
        }
    }


    function valDecimales($decimales)
    {
        if (strpos($decimales, '.') !== FALSE) {
            $varDecimal = explode(".", $decimales);
            if (strlen($varDecimal[1]) < 2 || strlen($varDecimal[1]) > 2) {
                return 1;
            } else {
                if (is_numeric($varDecimal[0]) && is_numeric($varDecimal[1])) {
                    return 0;
                } else {
                    return 1;
                }
            }
        } else {
            return 1;
        }
    }




    function valCorreo($email)
    {
        $patron = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/';

        if (preg_match($patron, $email)) {
            return 0; // El formato es válido
        } else {
            return 1; // El formato NO es válido
        }
    }

    function valCuentaBanco($cuenta)
    {
        $cuenta = trim($cuenta);
        if (strlen($cuenta) == 20) {
            if ($this->valDigitos($cuenta) == 0) {
                return 0;
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }


    function valCuentaBancoActivo($cuenta)
    {
        if ($this->valCuentaBanco($cuenta) == 0) {
            if (substr($cuenta, 0, 4) == '0171') {
                return 0;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }




    function valFecha($fecha)
    {

        $swSeguir = 0;

        if (strlen($fecha) == 8) {
            if ($this->valDigitos($fecha) == 0) {
                $dia = substr($fecha, 0, 2) * 1;
                $mes = substr($fecha, 2, 2) * 1;
                $anio = substr($fecha, 4, 4) * 1;
                $fechaValor = $dia . "-" . $mes . "-" . $anio;

                $diaFebrero = 28;
                if ($anio % 4 == 0) {
                    $diaFebrero = 29;
                }

                $anioActual = date('Y');
                if ($anioActual == $anio || $anio >  $anioActual) {
                    $swSeguir = 1;
                } else {
                    return 1;
                }

                if ($swSeguir == 1) {
                    if ($mes < 0 || $mes > 12) {
                        return 1;
                    } else {
                        if ($dia < 0 || $dia > 31) {
                            return 1;
                        }

                        switch ($mes) {
                            case 1:
                            case 3:
                            case 5:
                            case 7:
                            case 10:
                            case 12:
                                if ($dia > 31) {
                                    return 1;
                                }
                                break;
                            case 4:
                            case 6:
                            case 8:
                            case 9:
                            case 11:
                                if ($dia > 30) {
                                    return 1;
                                }
                                break;
                            case 2:
                                if ($dia > $diaFebrero) {
                                    return 1;
                                }
                                break;
                            default:
                                return 1;
                                break;
                        }
                    }

                    //Validar que la fecha de pago (Fecha valor sea igual o mayor a la fecha actual
                    $diaActualJuliano = gregoriantojd(date('m'), date('d'), date('Y'));
                    $diaPagoJuliano =   gregoriantojd($mes, $dia, $anio);  
                    if ($diaActualJuliano > $diaPagoJuliano) {
                        //Si la fecha de pago es menor a la fecha actual emitir error
                        return 2;
                    }                    
                }
            }else{
                return 1; 
            }

        } else {
            return 1;
        }
    }


    public function valTipoOrden($tipoOrden)
    {
        $tOrden = strtoupper($tipoOrden);
        if ($tOrden == 'SAL' || $tOrden == 'SCV') {
            return 0;
        } else {
            return 1;
        }
    }



    public function obtenerDatosBanco($arrgBanco, $codBanco){
        $arrgBanco = $arrgBanco['BNKSRDSJVReturn']['BNKSRDSJV'];
        $codBanco = str_pad($codBanco, 9, "0", STR_PAD_LEFT);

        $i = 0;
        $swBanco = 0;
        foreach ($arrgBanco as $banco) {
            if($codBanco == $banco['BNKID']){
                $data = [
                   "resp" => 0,
                   "arrg"=> $banco,
                ];
                $swBanco = 1;
                break;
            }    
            $i++;
        }
        if($swBanco == 0){
               $data = [
                   "resp"=>  "1",
                   "arrg"=> "NO ENCONTRADO"
                ];
        }
        return $data;
    }



    //fin del trait
}
