<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use App\Models\Balance;
use App\Models\Cliente;
use App\Models\Bitacora;
use App\Models\Dictamen;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\Archivo;
use App\Models\ArchivoMensual;
use App\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponder;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class EmpresaController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $empresas = Empresa::paginate(15);
        $empresas = EmpresaResource::collection($empresas)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $empresas;
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'version' => 'required|numeric',
                'descripcion' => 'required|string|max:255',
                'rfc_contribuyente' => 'required|string|max:50',
                'rfc_representante' => 'required|string|max:50',
                'proveedor' => 'required|string|max:50',
                'tipo_caracter' => 'required|string|max:50',
                'modalidad_permiso' => 'required|string|max:50',
                'num_permiso' => 'required|string|max:50',
                'clave_instalacion' => 'required|string|max:50',
                'descripcion_instalacion' => 'required|string|max:255',
                'geolocalizacion_latitud' => 'required|string|max:100',
                'geolocalizacion_longitud' => 'required|string|max:100',
                'numero_tanques' => 'required|numeric',
                'numero_ductos_entradas_salidas' => 'required|numeric',
                'numero_ductos_distribucion' => 'required|numeric',
                'fecha_hora_corte' => 'required|date_format:Y-m-d H:i:s',
                'producto_omision' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }
            
            $empresa = new Empresa($request->all());
            $empresa->save();

            $resource = new EmpresaResource($empresa);

            return $this->success('Empresa registrada correctamente.', [
                'empresa' => $resource
            ]);
        } catch (\Throwable $th) {
            return $this->error("Error al registrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function show($idEmpresa)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();

            if ($empresa == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }

            $resource = new EmpresaResource($empresa);

            return $this->success('Información consultada correctamente.', [
                'empresa' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al mostrar el registro, error:{$th->getMessage()}.");
        }
    }

    public function update(Request $request, $idEmpresa)
    {
        try {
            $rules = [
                'version' => 'required|numeric',
                'descripcion' => 'required|string|max:255',
                'rfc_contribuyente' => 'required|string|max:50',
                'rfc_representante' => 'required|string|max:50',
                'proveedor' => 'required|string|max:50',
                'tipo_caracter' => 'required|string|max:50',
                'modalidad_permiso' => 'required|string|max:50',
                'num_permiso' => 'required|string|max:50',
                'clave_instalacion' => 'required|string|max:50',
                'descripcion_instalacion' => 'required|string|max:255',
                'geolocalizacion_latitud' => 'required|string|max:100',
                'geolocalizacion_longitud' => 'required|string|max:100',
                'numero_tanques' => 'required|numeric',
                'numero_ductos_entradas_salidas' => 'required|numeric',
                'numero_ductos_distribucion' => 'required|numeric',
                'fecha_hora_corte' => 'required|date_format:Y-m-d H:i:s',
                'producto_omision' => 'required|numeric',
            ];

            $validator = Validator::make( $request->all(), $rules, $messages = [
                'required' => 'El campo :attribute es requerido.',
                'numeric' => 'El campo :attribute debe ser númerico.',
                'string' => 'El campo :attribute debe ser tipo texto.',
                'max' => 'El campo :attribute excede el tamaño requerido (:max).',
                'date_format' => 'El campo :attribute debe tener formato fecha (Y-m-d) ó formato fecha hora (YYYY-MM-DD HH:mm:ss)',
            ]);
            
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                return $this->error("Error al actualizar el registro", $errors);
            }

            $empresa = Empresa::where('id', $idEmpresa)->first();
            if ($empresa == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }
            $empresa->version = $request->version;
            $empresa->descripcion = $request->descripcion;
            $empresa->rfc_contribuyente = $request->rfc_contribuyente;
            $empresa->rfc_representante = $request->rfc_representante;
            $empresa->proveedor = $request->proveedor;
            $empresa->tipo_caracter = $request->tipo_caracter;
            $empresa->modalidad_permiso = $request->modalidad_permiso;
            $empresa->num_permiso = $request->num_permiso;
            $empresa->clave_instalacion = $request->clave_instalacion;
            $empresa->descripcion_instalacion = $request->descripcion_instalacion;
            $empresa->geolocalizacion_latitud = $request->geolocalizacion_latitud;
            $empresa->geolocalizacion_longitud = $request->geolocalizacion_longitud;
            $empresa->numero_tanques = $request->numero_tanques;
            $empresa->numero_ductos_entradas_salidas = $request->numero_ductos_entradas_salidas;
            $empresa->numero_ductos_distribucion = $request->numero_ductos_distribucion;
            $empresa->fecha_hora_corte = $request->fecha_hora_corte;
            $empresa->producto_omision = $request->producto_omision;
            $empresa->save();

            $cambios = '';
            foreach ($empresa->getChanges() as $key => $value) {
                $cambios .= $key . ' - ' . $value . ' | ';
            }

            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 1;
            $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
            $bitacora->descripcion2 = 'modificó la empresa ' . $empresa->id;
            $bitacora->descripcion3 = $cambios;
            $bitacora->usuario_id = $request->user()->id;
            $bitacora->save();

            $resource = new EmpresaResource($empresa);

            return $this->success('Registro actualizado correctamente.', [
                'empresa' => $resource
            ]);

        } catch (\Throwable $th) {
            return $this->error("Error al actualizar el registro, error:{$th->getMessage()}.");
        }
    }

    public function destroy($idEmpresa, Request $request)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();

            if ($empresa == NULL)
            {
                return $this->error("Error, NO se encontró el registro.");
            }

            $empresa->delete();

            $resource = new EmpresaResource($empresa);

            return $this->success('Registro borrado correctamente.', [
                'empresa' => $resource
            ]);

            
        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }

    public function crearJsonV1 (Request $request) {

        try {
            /*
                ** Función para generar archivo json de volumétricos **
            */

            /* Obtener datos de la Request */
            $idEmpresa = $request->idEmpresa;
            $fechaBalance = $request->fechaBalance;
            $tipo = $request->tipo;
            $unidad = $request->unidad;
            

            /* Obtener datos de la empresa */
            $empresa = Empresa::where('id', $idEmpresa)->first();
            
            /* Obtener productos, y el producto por omisión */
            $empresa->load('productoOmision');

            /* Obtener los datos del producto por omisión */
            $productoOmision = $empresa->productoOmision;
            $compuestos = $productoOmision->compuestos;
            

            $propano = $compuestos[0];
            $butano = $compuestos[1];
            
            
            /* Obtener el balance de la fecha solicitada*/
            $balance = Balance::where('fecha', $fechaBalance)->first();
            
            /* Obtener los (dictamenes) el balance de la fecha solicitada    (duda )*/
            $balance->load('dictamenes');
            
            
            /* Obtener la (densidad) el balance de la fecha solicitada    (duda )*/
            $balance->load('densidad');
            $densidadBalance = $balance->densidad->densidad;
            
            /* Tomar el porcentaje de entrada de acuerdo a las ventas */
            $fechaHoraCorteDT = new DateTime($fechaBalance . " 05:00:00", new DateTimeZone('America/Mexico_City'));
            $fechaHoraCorte = $fechaHoraCorteDT->format(DateTime::ATOM);
            $fechaFolioDictamen = date('Y', strtotime($fechaBalance));
            $horaCorte = substr($fechaHoraCorte, 11);
            $fechaHoraCorteAntStr = date('Y-m-d', strtotime($fechaHoraCorte.'-1 days'));
            $fechaHoraCorteAntDT = new DateTime($fechaHoraCorteAntStr . " 05:00:00", new DateTimeZone('America/Mexico_City'));
            $fechaHoraCorteAnt = $fechaHoraCorteAntDT->format(DateTime::ATOM);

            $totalVentasIrge_DB = DB::table('balances_duca.salidas')
                ->select(DB::raw('SUM(valor) AS masa'))
                ->whereRaw("balance_id = ? AND tipo = ?", [$balance->id, 'l'])
                ->get();
                
            $totalVentasIrge = $totalVentasIrge_DB[0]->masa;
            #dd($totalVentasIrge);
            

            $ventasIrge = DB::table('balances_duca.salidas')
                    ->select(DB::raw('SUM(valor) AS masa'), DB::raw('cliente'))
                    ->whereRaw("balance_id = ? AND tipo = ?", [$balance->id, 'l'])
                    ->groupBy('cliente')
                    ->get();

            $clientesVentas = [];
            foreach ($ventasIrge as $venta) {
                $porcCliente = ($venta->masa * 100) / $totalVentasIrge;
                $client = Cliente::where('id', $venta->cliente)->first();
                
                $raw = array(
                    'cliente' => $client,
                    'masa' => $venta->masa,
                    'porcentaje' => $porcCliente
                );
                array_push($clientesVentas, $raw);
            }
            

            /* Obtener el dictamen que se usará */
            $dictamenes = $balance->dictamenes;
            #dd($dictamenes);

            if (count($dictamenes) < 1) {
                $bitacora = new Bitacora();
                $bitacora->fecha = date('Y-m-d');
                $bitacora->fecha_hora = date('Y-m-d H:i:s');
                $bitacora->evento_id = 19;
                $bitacora->descripcion1 = 'Error en generación de archivo json, no hay dictamen selecionado. ';
                $bitacora->descripcion2 = 'usuario: ' . $request->user()->usuario;
                $bitacora->descripcion3 = 'fecha ' . $fechaBalance;
                $bitacora->usuario_id = $request->user()->id;
                $bitacora->save();
                $bitacora->load('usuario');
                $bitacora->load('evento');
                $this->error("No se han creado dictámenes para el día " . $fechaBalance);
            }


            $recepcionGas = DB::table('balances_duca.entradas')
                ->select(DB::raw('MAX(valor) - MIN(valor) AS recibido'))
                ->whereRaw('balance_id = ?', [$balance->id])
                ->first();
            $volRecibido = is_null($recepcionGas->recibido) ? 0 : $recepcionGas->recibido;
            
            if (count($dictamenes) == 2) {
                /* Listar los dictámenes */
                
                $recibos = Recibo::where('balance_id', $balance->id_balance)->get();
                $recibos->load('dictamen');

                $recibosGas = [];
                $numEntregas = 0;
                $sumaEntregas = 0;
                $restanteEntradaEB00 = 0;
                $totalEntradaEB00 = 0;

                $entradas = [];
                $totalEntradasEB00 = 0; // $totalEntradasEntregas
                $inventarioInicial = 0;

                $numeroRecibosInDictamenes = 0;

                foreach ($dictamenes as $dic) {
                    $dic->load('recibos');
                    $numeroRecibosInDictamenes = $numeroRecibosInDictamenes + count($dic->recibos);
                }

                /* Por cada dictamen obtener los recibos */

                foreach ($dictamenes as $dic) {
                    $dic->load('recibos');
                    $dic->load('cliente');
                    /* dd($dic); */
                    $numFolioDictamenRec = '0';
                    switch (true) {
                        case $dic->id < 10:
                            $numFolioDictamenRec = "0000".$dic->id;
                            break;
                        case $dic->id >= 10 && $dic->id < 100:
                            $numFolioDictamenRec = "000".$dic->id;
                            break;
                        case $dic->id >= 100 && $dic->id < 1000:
                            $numFolioDictamenRec = "00".$dic->id;
                            break;
                        case $dic->id >= 1000 && $dic->id < 10000:
                            $numFolioDictamenRec = "0".$dictamenes->id;
                            break;
                    }
                    $folioDictamenRec = $dic->rfcDictamen . $numFolioDictamenRec . $fechaFolioDictamen;
                    //dd($folioDictamenRec);
                    //echo '******************************<br />';
                    //echo 'dictamen => ' . $dic->id . '<br />';
                    //echo 'balance => ' . $balance->id_balance . '<br />';
                    //echo 'volRecibido => ' . $volRecibido . '<br />';
                    $numRecibos = count($dic->recibos);
                    //echo 'num rec => ' . $numRecibos . '<br />';
                    $reciboTot = 0;
                    if ($numRecibos > 0 ) {
                        $recibos = $dic->recibos;
                        foreach ($recibos as $rec) {
                            //echo 'recibo => ' . $rec . '<br />';
                            $numEntregas++;
                            if ($numEntregas > 1) {
                                if ($numEntregas === $numeroRecibosInDictamenes) {
                                    //echo 'Num Entregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                    //echo 'Suma entregas => ' . $sumaEntregas . '<br />';
                                    //echo 'recibo => ' . $rec->recibo . '<br />';
                                    if ($sumaEntregas > 0) {
                                        $reciboTot = $volRecibido - $sumaEntregas;
                                    } else {
                                        $reciboTot = $volRecibido;
                                    }
                                    //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                    //echo '=================================<br />';
                                } else {
                                    //echo 'numEntregas => ' . $numEntregas . '<br />';
                                    //echo 'suma entregas => ' . $sumaEntregas . '<br />';
                                    $reciboTot = $rec->recibo;
                                    //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                    //echo '------------------------------<br />';
                                }
                            } else {
                                if ($numEntregas === $numeroRecibosInDictamenes) {
                                    //echo 'Num Entregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                    //echo 'Suma entregas => ' . $sumaEntregas . '<br />';
                                    $reciboTot = $volRecibido - $sumaEntregas;
                                    //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                    //echo '=================================<br />';

                                } else {
                                    $reciboTot = $rec->recibo;
                                    //echo 'numEntregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                    //echo 'suma entregas => ' . $sumaEntregas . '<br />';
                                    //echo 'reciboTot => ' . $reciboTot . '<br />';
                                    //echo '------------------------------<br />';

                                }
                            }
                            $sumaEntregas = $sumaEntregas + $reciboTot;
    
                            $obj = [
                                'recibo' => $reciboTot,
                                'dictamen' => $dic,
                                'dictamen_id' => $dic->id,
                                'dictamen_folio' => $folioDictamenRec,
                                'entrada' => $numEntregas,
                                'cliente' => $dic->cliente,
                                'cliente_id' => $dic->cliente->id,
                            ];
                            array_push($recibosGas, $obj);
                        }
                    }

                }

                /* dd($recibosGas); */
                /* Obtener las recepciones */
                if (count($recibosGas) > 0)
                {
                    foreach ($recibosGas as $entrada) {
                        /* dd($entrada); */
                        $totalEntradasEB00 = $totalEntradasEB00 + floatval($entrada['recibo']);
                        foreach ($clientesVentas as $clienteDespacho) {
                            $entradaDucto = [
                                "balance_id" => $balance->id,
                                "fecha_hora_inicio" => $fechaHoraCorteAnt,
                                "fecha_hora_fin" => $fechaHoraCorte,
                                'valor' => (floatval($entrada['recibo'] * $clienteDespacho['porcentaje'])) / 100,
                                'tipo' => 'd',
                                'cliente' => $clienteDespacho['cliente'],
                                'compania' => $clienteDespacho,
                                'dictamen' => $entrada['dictamen']
                            ];
                            
                            array_push($entradas, $entradaDucto);
                        }    
                    }
                } else {
                    $balance->load('dictamenes');
                    $dictamenSel = $balance->dictamenes[0];
                    foreach ($clientesVentas as $clienteDespacho) {
                        $entradaDucto = [
                            "balance_id" => $balance->id,
                            "fecha_hora_inicio" => $fechaHoraCorteAnt,
                            "fecha_hora_fin" => $fechaHoraCorte,
                            'valor' => ($volRecibido * $clienteDespacho['porcentaje']) / 100,
                            'tipo' => 'd',
                            'cliente' => $clienteDespacho['cliente'],
                            'compania' => $clienteDespacho,
                            'dictamen' => $dictamenSel->folio
                        ];
                        array_push($entradas, $entradaDucto);
                    }

                }
                
                foreach ($entradas as $entrada) {
                    $totalEntradasEB00 = $totalEntradasEB00 + floatval($entrada['valor']);
                }

                /* Checar la cantidad de gas recibido */
                $entregaLlenadera = DB::table('balances_duca.salidas')
                    ->select(DB::raw('SUM(valor) AS recibido'))
                    ->whereRaw("balance_id = ? AND tipo = ?", [$balance->id,'l'])
                    ->first();

                $volEntregadoLlenadera = is_null($entregaLlenadera->recibido) ? 0 : $entregaLlenadera->recibido;
                #dd($volEntregadoLlenadera);
                $volEntregado = $volEntregadoLlenadera;

                /* registrosEntradas -> Entradas EB00  */
                
                $recepcionesEB00 = []; // almacena los registros json del patin EB00
                $totalEntradaEB00 = $volRecibido; // Total de recibido del patin EB00
                $restanteEntradaEB00 = $volRecibido; // Restante de entregado en el patin EB00
                $totalEntrada = count($entradas);
                $numRegistro = 0;
                $sumaDocumentosRecepcion = 0;

                /* Crear registros de entradas */
                
                if ($volRecibido > 0) {
                    $rowRecepcion = [];
                    $fechaHoraInicioRecepcionDT = new DateTime($balance->fecha . " 05:00:00", new DateTimeZone('America/Mexico_City'));
                    $fechaHoraInicioRecepcion =  $fechaHoraInicioRecepcionDT->format(DateTime::ATOM);
                    
                    $fechaHoraFinRecepcionDT = new DateTime($balance->fecha . "05:00:00", new DateTimeZone('America/Mexico_City'));
                    $fechaHoraFinRecepcion =  $fechaHoraFinRecepcionDT->format(DateTime::ATOM);
                    $sumaRecepcionTanque = 0;

                    #$idTanque = $tanque['tanque_id'];
                    $idTanque = null;
                    $restanteAlmTanqueRec = 0;
                    $inventarioInicialRec = 0;
                    
                    #$registroAlmTanqueAnterior = ContenedorBalance::where('balance_id', '<', $balance->id)->where('contenedor_id', $idTanque)->orderBy('balance_id', 'desc')->first();
                    $registroAlmTanqueAnterior = 0;
                    $indexEntrada = 0;

                    if ($restanteEntradaEB00 > 0)   // Si aun queda gas darle entrada
                    {
                        /* dd($entradas); */
                        for ($i = $indexEntrada; $i < $totalEntrada; $i++) // Listamos las entragas
                        {
                            $masaEntrada = $entradas[$indexEntrada]['valor'];
                            $clienteEntrada = $entradas[$indexEntrada]['cliente']['nombreCliente'];
                            $rfcClienteEntrada = $entradas[$indexEntrada]['cliente']['rfcCliente'];
                            $nombreClienteEntrada = $entradas[$indexEntrada]['cliente']['nombreCliente'];
                            $fecha_hora_inicio = $fechaHoraCorte;
                            $fecha_hora_fin = $fechaHoraCorteAnt;
                            $dictamenRec = $entradas[$indexEntrada]['dictamen'];
                            
                            # Lógica de las Entradas
                            $masaRec = 0;
                            $volFinal = 0;

                            if ($restanteEntradaEB00 > 0) {

                                /* echo '******************************************<br />';
                                #echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                #dd($limiteTanqueRec > $restanteEntradaEB00);
                                */

                                $numRegistro = $numRegistro + 1;
                                $masaRec = $masaEntrada;
                                $volFinal = $inventarioInicialRec + $masaRec;
                                /* echo 'index: ' . $indexEntrada . ' - Restante entrada > limite Entrada<br />';
                                #echo "tanque => " . $tanque['tanque']->id . '<br />';
                                #echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                echo 'masa: ' . $masaRec . '<br />';
                                echo 'inv. final: ' . $volFinal . '<br />'; */

                                $dataRecepcion = [
                                    'NumeroDeRegistro' => $numRegistro,
                                    'VolumenPuntoEntrada:' => [
                                        'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec*1000) /$densidadBalance : $inventarioInicialRec),3),
                                        'UnidadDeMedida' => 'UM03'
                                    ],
                                    'VolumenRecepcion' => [
                                        'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                        'UnidadDeMedida' => 'UM03'
                                    ],
                                    'Temperatura' => 20,
                                    'PresionAbsoluta' => 101.325,
                                    'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                    'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                    'Complemento' => [
                                        'Dictamen' => [
                                            'RfcDictamen' => $dictamenRec->rfcDictamen,
                                            'LoteDictamen' => $dictamenRec->loteDictamen,
                                            'NumeroFolioDictamen' => $folioDictamenRec,
                                            'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                            'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                        ],
                                        'Nacional' => [
                                            'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                            'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                        ],
                                        'Aclaracion' => [
                                            'Aclaracion' => ""
                                        ]
                                    ]
                                    
                                ];
                                
                                $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                /* echo "restanteAlmTanqueRec: $restanteAlmTanqueRec <br />"; */
                                $restanteEntradaEB00 = $restanteEntradaEB00 - $masaRec;
                                /* echo "restanteEntradaEB00: $restanteEntradaEB00 <br />"; */
                                $sumaDocumentosRecepcion++;
                                /* echo "sumaDocumentosRecepcion: $sumaDocumentosRecepcion <br />"; */
                                $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                /* echo "sumaRecepcionTanque: $sumaRecepcionTanque <br />"; */
                                /* echo '----------------------------------------<br />'; */
                                $indexEntrada++;
                                /* echo "indexEntrada: $indexEntrada"; */
                                #array_push($rowRecepcion, $dataRecepcion);
                                array_push($recepcionesEB00, $dataRecepcion);
                                /*
                                if ($limiteTanqueRec > $restanteEntradaEB00) {
                                    if ($restanteEntradaEB00 > $limiteTanqueRec) {
                                        $numRegistro = $numRegistro + 1;
                                        $masaRec = $masaEntrada;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - Restante entrada > limite Entrada<br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';

                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec*1000) /$densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                            'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => ""
                                                ]
                                            ]
                                            
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaEB00 = 0;
                                        $sumaDocumentosRecepcion++;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                    } else {
                                        if ($restanteAlmTanqueRec <= 0) {
                                            break 1;
                                        }
                                        $numRegistro = $numRegistro + 1;
                                        
                                        $masaRec = $restanteEntradaEB00;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - Limite tanque > restante de almacen<br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                            'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => ""
                                                ]
                                            ]
                                                
                                            
                                        ];
                                        
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $restanteEntradaEB00 = 0;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        if ($restanteAlmTanqueRec <= 0) {
                                            $indexEntrada++;
                                        }
                                    }
                                    array_push($rowRecepcion, $dataRecepcion);
                                } else {
                                    $numRegistro = $numRegistro + 1;
                                    if ($masaEntrada > $limiteTanqueRec) {
                                        if ($restanteAlmTanqueRec <= 0) {
                                            break 1;
                                        }

                                        $masaRec = $limiteTanqueRec - $sumaRecepcionTanque;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - limite tanque menor a Restante entrada - Masa Entrada Mayor a limite <br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                                'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => $aclaracionRecepcion
                                                ]
                                            ]
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaEB00 = $restanteEntradaEB00 - $masaRec;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        //echo 'RestanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'SumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        array_push($rowRecepcion, $dataRecepcion);
                                        if ($restanteAlmTanqueRec <= 0) {
                                            $restanteAlmTanqueRec = 0;
                                            break 1;
                                        }
                                        
                                    } else {
                                        $masaRec = $masaEntrada;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - limite Entrada menor a Restante entrada, Masa Entrada menor a limite  <br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaMuelle: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                                'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => $aclaracionRecepcion
                                                ]
                                            ]
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaMuelle = $restanteEntradaMuelle - $masaRec;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        $indexEntrada++;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaMuelle: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        array_push($rowRecepcion, $dataRecepcion);

                                    }
                                }
                                */
                            }
                            #dd($rowRecepcion);


                        }
                    }
                    #array_push($recepcionesEB00, $rowRecepcion);
                }


                /* registrosVentas -> Salidas IRGE  */
                
                $salidas = Salida::where('balance_id', $balance->id)->where('tipo', 'l')->orderBy('id', 'asc')->get();
                $salidas->load('compania');
                #dd($salidas);
                
                $totalSalidasEntregas = 0;
                
                foreach ($salidas as $salida) {
                    #dd($salida);
                    $totalSalidasEntregas = $totalSalidasEntregas + floatval($salida['valor']);
                }
                #dd($totalSalidasEntregas);
                #dd('here');
                $restanteSalidas = $totalSalidasEntregas;
                $totalSalidas = count($salidas);
                $indexSalidas = 0;
                #dd($totalSalidas);

                $salidasTotales = [];
                $totalSalidaducto = $volEntregado;
                $sumaEntragadoCompleto = 0;
                $masaPendiente = 0;

                /* Obtener las salidas del día */
                $registrosVentas = Salida::where('balance_id', $balance->id)->get();
                /* $restanteEntradaEB00 = $volRecibido; se tiene que quitar ?*/
                
                $sumaDocumentosSalida = 0;
                $sumaEntregadoInTanque = 0;
                
                #dd($volEntregado);
                if ($volEntregado > 0) // Si hubo una salida, ya sea ducto y/o llenadera
                {
                    $rowSalida = []; // Fila que contendrá la data del json
                    $idTanque = null; // $tanque['tanque_id']; // id del tanque

                    $restanteAlmTanqueSal = 0;
                    $inventarioInicialSal = 0;

                    if ($restanteSalidas > 0)   // Si aun queda gas darle salida
                    {
                        
                        for ($i = $indexSalidas; $i < $totalSalidas; $i++) // Listamos las salidas
                        {
                            $masaSalida = 0;
                            $companiaEntrega = '';
                            $clienteEntrega = '';
                            $rfcClienteEntrega = '';
                            $nombreClienteEntrega = '';
                            $fecha_hora_inicio = '';
                            $fecha_hora_fin = '';

                            $masaSalida = $salidas[$i]->valor;
                            $fecha_hora_inicio = $salidas[$i]->fecha_hora_inicio;
                            $fecha_hora_fin = $salidas[$i]->fecha_hora_fin;
                            if ($salidas[$i]->cliente == 4) {
                                $companiaEntrega = Cliente::where('id', 1)->first();
                            } else {
                                $companiaEntrega = $salidas[$i]->compania;
                            } 
                            $rfcClienteEntrega = $companiaEntrega->rfcCliente;
                            $nombreClienteEntrega = $companiaEntrega->nombreCliente;

                            $fecha_hora_inicioDT = new DateTime($fecha_hora_inicio, new DateTimeZone('America/Mexico_City'));
                            $fecha_hora_inicio =  $fecha_hora_inicioDT->format(DateTime::ATOM);
                            $fecha_hora_finDT = new DateTime($fecha_hora_fin, new DateTimeZone('America/Mexico_City'));
                            $fecha_hora_fin =  $fecha_hora_finDT->format(DateTime::ATOM);
                            
                            
                            /* Determinar que dictamen se usará a partir de la entrada, tomaremos el balance */
                            /* dd($recibosGas);
                            dd($volEntregado); */
                            $dictamenSal = new Dictamen();
                            foreach ($recibosGas as $recibo) {
                                /* dd($recibo); */
                                if ($sumaEntragadoCompleto <= $recibo['recibo']) {
                                    /* echo '--------<br />';
                                    echo "sumaEntragadoCompleto: $sumaEntragadoCompleto<br />";
                                    echo "recibo['recibo']: " . $recibo['recibo'] . "<br />"; */
                                    $dictamenSal = $recibo['dictamen'];
                                    /* echo "dictamenSal: " . $recibo['dictamen']['folioDictamen'] . "<br />"; */
                                    break 1;
                                }
                            }
                            
                            # Logica de las Salidas
                            $masa = $masaSalida;
                            $numRegistro = $numRegistro + 1;
                            $dataEntrega = [
                                'NumeroDeRegistro' => $numRegistro,
                                'VolumenPuntoSalida' => [
                                    'ValorNumerico' => round($this->convertLitros($unidad, $inventarioInicialSal, $salidas[$i]->densidad),3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'VolumenEntregado' => [
                                    'ValorNumerico' => round($this->convertLitros($unidad, $masa, $salidas[$i]->densidad),3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'Temperatura' => 20,
                                'PresionAbsoluta' => 101.325,
                                'FechaYHoraInicialEntrega' => $fecha_hora_inicio,
                                'FechaYHoraFinalEntrega' => $fecha_hora_fin,
                                'Complemento' => [
                                    'Dictamen' => [
                                        'RfcDictamen' => $dictamenSal->rfcDictamen,
                                        'LoteDictamen' => $dictamenSal->loteDictamen,
                                        'NumeroFolioDictamen' => $dictamenSal->folioDictamen,
                                        'FechaEmisionDictamen' => $dictamenSal->fechaEmisionDictamen,
                                        'ResultadoDictamen' => $dictamenSal->resultadoDictamen
                                    ],
                                    'Nacional' => [
                                        'RfcClienteOProveedor' => $rfcClienteEntrega,
                                        'NombreClienteOProveedor' => $nombreClienteEntrega,
                                    ],
                                    'Aclaracion' => [
                                        'Aclaracion' => ""
                                    ]
                                ]
                            ];
                            #dd($dataEntrega);
                            #$sumaEntregadoInTanque = $sumaEntregadoInTanque + $masa;
                            //echo "sumaEntregadoInTanque  + masa-> " . $sumaEntregadoInTanque . "<br />";
                            $sumaEntragadoCompleto = $sumaEntragadoCompleto + $masa;
                            //echo "sumaEntragadoCompleto  + masa-> " . $sumaEntragadoCompleto . "<br />";
                            $restanteAlmTanqueSal = $restanteAlmTanqueSal - $masa;
                            //echo "restanteAlmTanqueSal  - masa-> " . $restanteAlmTanqueSal . "<br />";
                            $inventarioInicialSal = $inventarioInicialSal - $masa;
                            //echo "inventarioInicialSal  - masa-> " . $inventarioInicialSal . "<br />";
                            $sumaDocumentosSalida++;
                            $restanteSalidas = $restanteSalidas - $masa;
                            //echo "restanteSalidas  - masa -> " . $restanteSalidas . "<br />";
                            //echo "*********************************************<br/>";
                            $indexSalidas++;
                            #array_push($rowSalida, $dataEntrega);
                            array_push($salidasTotales, $dataEntrega);
                        }
                        #array_push($salidasTotales, $rowSalida);
                    }
                }



            } else {
                $dictamenes->load('cliente');
                $dictamenes->load('recibos');
                $recibosGas = [];
                $numEntregas = 0;
                $sumaEntregas = 0;
                $numeroRecibosInDictamenes = 0;
                $dictamenSel = $dictamenes[0];
                $numeroRecibosInDictamenes = $numeroRecibosInDictamenes + count($balance->recibos);

                $numFolioDictamen = '0';
                switch (true) {
                    case $dictamenSel->id < 10:
                        $numFolioDictamen = "0000".$dictamenSel->id;
                        break;
                    case $dictamenSel->id >= 10 && $dictamenSel->id < 100:
                        $numFolioDictamen = "000".$dictamenSel->id;
                        break;
                    case $dictamenSel->id >= 100 && $dictamenSel->id < 1000:
                        $numFolioDictamen = "00".$dictamenSel->id;
                        break;
                    case $dictamenSel->id >= 1000 && $dictamenSel->id < 10000:
                        $numFolioDictamen = "0".$dictamenes->id;
                        break;
                }

                $folioDictamen = $dictamenSel->rfcDictamen . $numFolioDictamen . $fechaFolioDictamen;

                /* Checar la cantidad de gas recibido */

                $entregaLlenadera = DB::table('balances_duca.salidas')
                    ->select(DB::raw('SUM(valor) AS recibido'))
                    ->whereRaw("balance_id = ? AND tipo = ?", [$balance->id,'l'])
                    ->first();
                #dd($entregaLlenadera);

                $balance->load('recibos');
                $numRecibos = count($balance->recibos);
                # dd($numRecibos);

                $reciboTot = 0;
                $volEntregadoLlenadera = is_null($entregaLlenadera->recibido) ? 0 : $entregaLlenadera->recibido;
                $volEntregado = $volEntregadoLlenadera;

                if ($numRecibos > 0 ) {
                    $recibos = $balance->recibos;
                    foreach ($recibos as $rec) {
                        $rec->load('dictamen');
                        $dictamenSel = $rec->dictamen;
                        $dictamenSel->load('cliente');
                        //dd($dictamenSel);
                        //echo 'recibo => ' . $rec . '<br />';
                        $numEntregas++;
                        if ($numEntregas > 1) {
                            if ($numEntregas === $numeroRecibosInDictamenes) {
                                //echo 'Num Entregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                //echo 'Suma entregas => ' . $sumaEntregas . '<br />';
                                //echo 'recibo => ' . $rec->recibo . '<br />';
                                if ($sumaEntregas > 0) {
                                    $reciboTot = $volRecibido - $sumaEntregas;
                                } else {
                                    $reciboTot = $volRecibido;
                                }
                                //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                //echo '=================================<br />';
                            } else {
                                //echo 'numEntregas => ' . $numEntregas . '<br />';
                                //echo 'suma entregas => ' . $sumaEntregas . '<br />';
                                $reciboTot = $rec->recibo;
                                //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                //echo '------------------------------<br />';
                            }
                        } else {
                            if ($numEntregas === $numeroRecibosInDictamenes) {
                                //echo 'Num Entregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                //echo 'Suma entregas => ' . $sumaEntregas . '<br />';
                                $reciboTot = $volRecibido - $sumaEntregas;
                                //echo 'reciboTotal => ' . $reciboTot . '<br />';
                                //echo '=================================<br />';

                            } else {
                                $reciboTot = $rec->recibo;
                                //echo 'numEntregas => ' . $numEntregas . ' de ' . $numeroRecibosInDictamenes . '<br />';
                                //echo 'suma entregas => ' . $sumaEntregas . '<br />';
                                //echo 'reciboTot => ' . $reciboTot . '<br />';
                                //echo '------------------------------<br />';

                            }
                        }
                        $sumaEntregas = $sumaEntregas + $reciboTot;

                        $obj = [
                            'recibo' => $reciboTot,
                            'dictamen' => $dictamenSel,
                            'dictamen_id' => $dictamenSel->id,
                            'entrada' => $numEntregas,
                            'cliente' => $dictamenSel->cliente,
                            'cliente_id' => $dictamenSel->id,
                        ];
                        array_push($recibosGas, $obj);
                    }
                } else 
                {
                    $balance->load('dictamenes');
                    $dictamenSel = $balance->dictamenes[0];
                }
                # Checar la cantidad de gas recibido
                
                $restanteEntradaEB00 = 0;
                $totalEntradaEB00 = 0;

                $entradas = [];
                $totalEntradasEB00 = 0; // $totalEntradasEntregas
                $inventarioInicial = 0;

                /* Obtener las recepciones */
                if (count($recibosGas) > 0)
                {
                    foreach ($recibosGas as $entrada) {
                        $totalEntradasEB00 = $totalEntradasEB00 + floatval($entrada['recibo']);
                        foreach ($clientesVentas as $clienteDespacho) {
                            $entradaDucto = [
                                "balance_id" => $balance->id,
                                "fecha_hora_inicio" => $fechaHoraCorteAnt,
                                "fecha_hora_fin" => $fechaHoraCorte,
                                'valor' => (floatval($entrada['recibo'] * $clienteDespacho['porcentaje'])) / 100,
                                'tipo' => 'd',
                                'cliente' => $clienteDespacho['cliente'],
                                'compania' => $clienteDespacho,
                                'dictamen' => $dictamenSel
                            ];
                            array_push($entradas, $entradaDucto);
                        }    
                    }
                } else {
                    foreach ($clientesVentas as $clienteDespacho) {
                        $entradaDucto = [
                            "balance_id" => $balance->id,
                            "fecha_hora_inicio" => $fechaHoraCorteAnt,
                            "fecha_hora_fin" => $fechaHoraCorte,
                            'valor' => ($volRecibido * $clienteDespacho['porcentaje']) / 100,
                            'tipo' => 'd',
                            'cliente' => $clienteDespacho['cliente'],
                            'compania' => $clienteDespacho,
                            'dictamen' => $dictamenSel
                        ];
                        array_push($entradas, $entradaDucto);
                    }

                }
                
                foreach ($entradas as $entrada) {
                    $totalEntradasEB00 = $totalEntradasEB00 + floatval($entrada['valor']);
                }


                #dd($totalEntradasEB00);
                
                /* registrosEntradas -> Entradas EB00  */
                
                $recepcionesEB00 = []; // almacena los registros json del patin EB00
                $totalEntradaEB00 = $volRecibido; // Total de recibido del patin EB00
                $restanteEntradaEB00 = $volRecibido; // Restante de entregado en el patin EB00
                $totalEntrada = count($entradas);
                $numRegistro = 0;
                $sumaDocumentosRecepcion = 0;

                /* Crear registros de entradas */
                
                if ($volRecibido > 0) {
                    $rowRecepcion = [];
                    $fechaHoraInicioRecepcionDT = new DateTime($balance->fecha . " 05:00:00", new DateTimeZone('America/Mexico_City'));
                    $fechaHoraInicioRecepcion =  $fechaHoraInicioRecepcionDT->format(DateTime::ATOM);
                    
                    $fechaHoraFinRecepcionDT = new DateTime($balance->fecha . "05:00:00", new DateTimeZone('America/Mexico_City'));
                    $fechaHoraFinRecepcion =  $fechaHoraFinRecepcionDT->format(DateTime::ATOM);
                    $sumaRecepcionTanque = 0;

                    #$idTanque = $tanque['tanque_id'];
                    $idTanque = null;
                    $restanteAlmTanqueRec = 0;
                    $inventarioInicialRec = 0;
                    
                    #$registroAlmTanqueAnterior = ContenedorBalance::where('balance_id', '<', $balance->id)->where('contenedor_id', $idTanque)->orderBy('balance_id', 'desc')->first();
                    $registroAlmTanqueAnterior = 0;
                    $indexEntrada = 0;
                    
                    if ($restanteEntradaEB00 > 0)   // Si aun queda gas darle entrada
                    {
                        #dd($entradas);
                        for ($i = $indexEntrada; $i < $totalEntrada; $i++) // Listamos las entragas
                        {
                            $masaEntrada = $entradas[$indexEntrada]['valor'];
                            $clienteEntrada = $entradas[$indexEntrada]['cliente']['nombreCliente'];
                            $rfcClienteEntrada = $entradas[$indexEntrada]['cliente']['rfcCliente'];
                            $nombreClienteEntrada = $entradas[$indexEntrada]['cliente']['nombreCliente'];
                            $fecha_hora_inicio = $fechaHoraCorte;
                            $fecha_hora_fin = $fechaHoraCorteAnt;
                            $dictamenRec = $entradas[$indexEntrada]['dictamen'];

                            
                            # Lógica de las Entradas
                            $masaRec = 0;
                            $volFinal = 0;

                            if ($restanteEntradaEB00 > 0) {

                                /* echo '******************************************<br />';
                                #echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                #dd($limiteTanqueRec > $restanteEntradaEB00);
                                */

                                $numRegistro = $numRegistro + 1;
                                $masaRec = $masaEntrada;
                                $volFinal = $inventarioInicialRec + $masaRec;
                                /* echo 'index: ' . $indexEntrada . ' - Restante entrada > limite Entrada<br />';
                                #echo "tanque => " . $tanque['tanque']->id . '<br />';
                                #echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                echo 'masa: ' . $masaRec . '<br />';
                                echo 'inv. final: ' . $volFinal . '<br />'; */

                                $dataRecepcion = [
                                    'NumeroDeRegistro' => $numRegistro,
                                    'VolumenPuntoEntrada:' => [
                                        'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec*1000) /$densidadBalance : $inventarioInicialRec),3),
                                        'UnidadDeMedida' => 'UM03'
                                    ],
                                    'VolumenRecepcion' => [
                                        'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                        'UnidadDeMedida' => 'UM03'
                                    ],
                                    'Temperatura' => 20,
                                    'PresionAbsoluta' => 101.325,
                                    'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                    'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                    'Complemento' => [
                                        'Dictamen' => [
                                            'RfcDictamen' => $dictamenRec->rfcDictamen,
                                            'LoteDictamen' => $dictamenRec->loteDictamen,
                                            'NumeroFolioDictamen' => $folioDictamen,
                                            'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                            'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                        ],
                                        'Nacional' => [
                                            'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                            'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                        ],
                                        'Aclaracion' => [
                                            'Aclaracion' => ""
                                        ]
                                    ]
                                    
                                ];
                                #dd('here');
                                $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                /* echo "restanteAlmTanqueRec: $restanteAlmTanqueRec <br />"; */
                                $restanteEntradaEB00 = $restanteEntradaEB00 - $masaRec;
                                /* echo "restanteEntradaEB00: $restanteEntradaEB00 <br />"; */
                                $sumaDocumentosRecepcion++;
                                /* echo "sumaDocumentosRecepcion: $sumaDocumentosRecepcion <br />"; */
                                $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                /* echo "sumaRecepcionTanque: $sumaRecepcionTanque <br />"; */
                                /* echo '----------------------------------------<br />'; */
                                $indexEntrada++;
                                /* echo "indexEntrada: $indexEntrada"; */
                                #array_push($rowRecepcion, $dataRecepcion);
                                array_push($recepcionesEB00, $dataRecepcion);
                                /*
                                if ($limiteTanqueRec > $restanteEntradaEB00) {
                                    if ($restanteEntradaEB00 > $limiteTanqueRec) {
                                        $numRegistro = $numRegistro + 1;
                                        $masaRec = $masaEntrada;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - Restante entrada > limite Entrada<br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';

                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec*1000) /$densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                            'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => ""
                                                ]
                                            ]
                                            
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaEB00 = 0;
                                        $sumaDocumentosRecepcion++;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                    } else {
                                        if ($restanteAlmTanqueRec <= 0) {
                                            break 1;
                                        }
                                        $numRegistro = $numRegistro + 1;
                                        
                                        $masaRec = $restanteEntradaEB00;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - Limite tanque > restante de almacen<br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                            'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => ""
                                                ]
                                            ]
                                                
                                            
                                        ];
                                        
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $restanteEntradaEB00 = 0;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        if ($restanteAlmTanqueRec <= 0) {
                                            $indexEntrada++;
                                        }
                                    }
                                    array_push($rowRecepcion, $dataRecepcion);
                                } else {
                                    $numRegistro = $numRegistro + 1;
                                    if ($masaEntrada > $limiteTanqueRec) {
                                        if ($restanteAlmTanqueRec <= 0) {
                                            break 1;
                                        }

                                        $masaRec = $limiteTanqueRec - $sumaRecepcionTanque;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - limite tanque menor a Restante entrada - Masa Entrada Mayor a limite <br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaEB00 . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                                'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => $aclaracionRecepcion
                                                ]
                                            ]
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaEB00 = $restanteEntradaEB00 - $masaRec;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        //echo 'RestanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaEB00: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'SumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        array_push($rowRecepcion, $dataRecepcion);
                                        if ($restanteAlmTanqueRec <= 0) {
                                            $restanteAlmTanqueRec = 0;
                                            break 1;
                                        }
                                        
                                    } else {
                                        $masaRec = $masaEntrada;
                                        $volFinal = $inventarioInicialRec + $masaRec;
                                        //echo '----------------------<br />';
                                        //echo 'index: ' . $indexEntrada . ' - limite Entrada menor a Restante entrada, Masa Entrada menor a limite  <br />';
                                        //echo "tanque => " . $tanque['tanque']->id . '<br />';
                                        //echo 'limiteTanqueRec: ' . $limiteTanqueRec . '<br />';
                                        //echo 'inventarioInicialRec: ' . $inventarioInicialRec . '<br />';
                                        //echo 'restanteEntradaMuelle: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'masaEntrada: ' . $masaEntrada . '<br />';
                                        //echo 'masa: ' . $masaRec . '<br />';
                                        //echo 'inv. final: ' . $volFinal . '<br />';
                                        
                                        $dataRecepcion = [
                                            'NumeroDeRegistro' => $numRegistro,
                                            'VolumenInicialTanque' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($inventarioInicialRec * 1000) / $densidadBalance : $inventarioInicialRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'VolumenFinalTanque' => round(($unidad === 'litros' ? ((($volFinal) * 1000) / $densidadBalance) : $volFinal),3),
                                            'VolumenRecepcion' => [
                                                'ValorNumerico' => round(($unidad === 'litros' ? ($masaRec*1000) /$densidadBalance : $masaRec),3),
                                                'UnidadDeMedida' => 'UM03'
                                            ],
                                            'Temperatura' => 20,
                                            'PresionAbsoluta' => 101.325,
                                            'FechaYHoraInicioRecepcion' => $fechaHoraInicioRecepcion,
                                                'FechaYHoraFinalRecepcion' => $fechaHoraFinRecepcion,
                                            'Complemento' => [
                                                'Dictamen' => [
                                                    'RfcDictamen' => $dictamenRec->rfcDictamen,
                                                    'LoteDictamen' => $dictamenRec->loteDictamen,
                                                    'NumeroFolioDictamen' => $folioDictamen,
                                                    'FechaEmisionDictamen' => $dictamenRec->fechaEmisionDictamen,
                                                    'ResultadoDictamen' => $dictamenRec->resultadoDictamen
                                                ],
                                                'Nacional' => [
                                                    'RfcClienteOProveedor' => $dictamenRec->cliente->rfcCliente,
                                                    'NombreClienteOProveedor' => $dictamenRec->cliente->nombreCliente,
                                                ],
                                                'Aclaracion' => [
                                                    'Aclaracion' => $aclaracionRecepcion
                                                ]
                                            ]
                                        ];
                                        $restanteAlmTanqueRec = $restanteAlmTanqueRec + $masaRec;
                                        $restanteEntradaMuelle = $restanteEntradaMuelle - $masaRec;
                                        $sumaRecepcionTanque = $sumaRecepcionTanque + $masaRec;
                                        $sumaDocumentosRecepcion++;
                                        $indexEntrada++;
                                        //echo 'restanteAlmTanqueRec: ' . $restanteAlmTanqueRec . '<br />';
                                        //echo 'restanteEntradaMuelle: ' . $restanteEntradaMuelle . '<br />';
                                        //echo 'sumaRecepcionTanque: ' . $sumaRecepcionTanque . '<br />';
                                        //echo '----------------------<br />';
                                        array_push($rowRecepcion, $dataRecepcion);

                                    }
                                }
                                */
                            }
                            #dd($rowRecepcion);


                        }
                    }
                    #array_push($recepcionesEB00, $rowRecepcion);
                }
                
                /* registrosVentas -> Salidas IRGE  */
                
                $salidas = Salida::where('balance_id', $balance->id)->where('tipo', 'l')->orderBy('id', 'asc')->get();
                $salidas->load('compania');
                
                $totalSalidasEntregas = 0;
                
                foreach ($salidas as $salida) {
                    #dd($salida);
                    $totalSalidasEntregas = $totalSalidasEntregas + floatval($salida['valor']);
                }
                #dd($totalSalidasEntregas);
                #dd('here');
                $restanteSalidas = $totalSalidasEntregas;
                $totalSalidas = count($salidas);
                $indexSalidas = 0;
                #dd($totalSalidas);

                $salidasTotales = [];
                $totalSalidaducto = $volEntregado;
                $sumaEntragadoCompleto = 0;
                $masaPendiente = 0;

                /* Obtener las salidas del día */
                $registrosVentas = Salida::where('balance_id', $balance->id)->get();
                /* $restanteEntradaEB00 = $volRecibido;  Se tiene que quitar ?*/
                
                $sumaDocumentosSalida = 0;
                $sumaEntregadoInTanque = 0;
                
                #dd($volEntregado);
                if ($volEntregado > 0) // Si hubo una salida, ya sea ducto y/o llenadera
                {
                    $rowSalida = []; // Fila que contendrá la data del json
                    $idTanque = null; // $tanque['tanque_id']; // id del tanque

                    $restanteAlmTanqueSal = 0;
                    $inventarioInicialSal = 0;

                    if ($restanteSalidas > 0)   // Si aun queda gas darle salida
                    {
                        
                        for ($i = $indexSalidas; $i < $totalSalidas; $i++) // Listamos las salidas
                        {
                            $masaSalida = 0;
                            $companiaEntrega = '';
                            $clienteEntrega = '';
                            $rfcClienteEntrega = '';
                            $nombreClienteEntrega = '';
                            $fecha_hora_inicio = '';
                            $fecha_hora_fin = '';

                            $masaSalida = $salidas[$i]->valor;
                            $fecha_hora_inicio = $salidas[$i]->fecha_hora_inicio;
                            $fecha_hora_fin = $salidas[$i]->fecha_hora_fin;
                            if ($salidas[$i]->cliente == 4) {
                                $companiaEntrega = Cliente::where('id', 1)->first();
                            } else {
                                $companiaEntrega = $salidas[$i]->compania;
                            } 
                            $rfcClienteEntrega = $companiaEntrega->rfcCliente;
                            $nombreClienteEntrega = $companiaEntrega->nombreCliente;

                            $fecha_hora_inicioDT = new DateTime($fecha_hora_inicio, new DateTimeZone('America/Mexico_City'));
                            $fecha_hora_inicio =  $fecha_hora_inicioDT->format(DateTime::ATOM);
                            $fecha_hora_finDT = new DateTime($fecha_hora_fin, new DateTimeZone('America/Mexico_City'));
                            $fecha_hora_fin =  $fecha_hora_finDT->format(DateTime::ATOM);
                            
                            # Logica de las Salidas
                            $masa = $masaSalida;
                            $numRegistro = $numRegistro + 1;
                            $dataEntrega = [
                                'NumeroDeRegistro' => $numRegistro,
                                'VolumenPuntoSalida' => [
                                    'ValorNumerico' => round($this->convertLitros($unidad, $inventarioInicialSal, $salidas[$i]->densidad),3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'VolumenEntregado' => [
                                    'ValorNumerico' => round($this->convertLitros($unidad, $masa, $salidas[$i]->densidad),3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'Temperatura' => 20,
                                'PresionAbsoluta' => 101.325,
                                'FechaYHoraInicialEntrega' => $fecha_hora_inicio,
                                'FechaYHoraFinalEntrega' => $fecha_hora_fin,
                                'Complemento' => [
                                    'Dictamen' => [
                                        'RfcDictamen' => $dictamenSel->rfcDictamen,
                                        'LoteDictamen' => $dictamenSel->loteDictamen,
                                        'NumeroFolioDictamen' => $dictamenSel->folioDictamen,
                                        'FechaEmisionDictamen' => $dictamenSel->fechaEmisionDictamen,
                                        'ResultadoDictamen' => $dictamenSel->resultadoDictamen
                                    ],
                                    'Nacional' => [
                                        'RfcClienteOProveedor' => $rfcClienteEntrega,
                                        'NombreClienteOProveedor' => $nombreClienteEntrega,
                                    ],
                                    'Aclaracion' => [
                                        'Aclaracion' => ""
                                    ]
                                ]
                            ];
                            #dd($dataEntrega);
                            #$sumaEntregadoInTanque = $sumaEntregadoInTanque + $masa;
                            //echo "sumaEntregadoInTanque  + masa-> " . $sumaEntregadoInTanque . "<br />";
                            $sumaEntragadoCompleto = $sumaEntragadoCompleto + $masa;
                            //echo "sumaEntragadoCompleto  + masa-> " . $sumaEntragadoCompleto . "<br />";
                            $restanteAlmTanqueSal = $restanteAlmTanqueSal - $masa;
                            //echo "restanteAlmTanqueSal  - masa-> " . $restanteAlmTanqueSal . "<br />";
                            $inventarioInicialSal = $inventarioInicialSal - $masa;
                            //echo "inventarioInicialSal  - masa-> " . $inventarioInicialSal . "<br />";
                            $sumaDocumentosSalida++;
                            $restanteSalidas = $restanteSalidas - $masa;
                            //echo "restanteSalidas  - masa -> " . $restanteSalidas . "<br />";
                            //echo "*********************************************<br/>";
                            $indexSalidas++;
                            #array_push($rowSalida, $dataEntrega);
                            array_push($salidasTotales, $dataEntrega);
                        }
                        #array_push($salidasTotales, $rowSalida);
                    }
                }
            }
            $medidoresArray = [];
            $medidoresRow = [
                /* 'SistemaMedicionTanque' => $tanque->sistema_medicion,
                'LocalizODescripSistMedicionTanque' => $tanque->localizcion_sistema_medicion,
                'VigenciaCalibracionSistMedicionTanque' => $tanque->vigencia_calibracion_sistema_medicion,
                'IncertidumbreMedicionSistMedicionTanque' => $tanque->incertidumbre_sistema_medicion */
                'SistemaMedicionDucto' => 'duda',
                'LocalizODescripSistMedicionDucto' => 'duda',
                'VigenciaCalibracionSistMedicionDucto' => 'duda',
                'CapacidadGasTalon' => [
                    'ValorNumerico' => 0,
                    'UnidadDeMedida' => 'UM03'
                ]
            ];
            array_push($medidoresArray, $medidoresRow);

            $ductoArray = [];

            $dataDucto = [
                'ClaveIdentificacionDucto' => 'Duda',
                'DescripcionDucto' => 'Duda',
                'DiametroDucto' => 'Duda',
                'SistemaMedicionDucto' => 'Duda',
                'LocalizODescripSistMedicionDucto' => 'Duda',
                'VigenciaCalibracionSistMedicionDucto' => 'Duda',
                'IncertidumbreMedicionSistMedicionDucto' => 'Duda',
                'CapacidadGasTalon' => [
                    'ValorNumerico' => 'Duda',
                    'UnidadDeMedida' => 'UM03'
                ],
                'Medidores' => $medidoresArray,
                'Recepciones' => [
                    'TotalRecepciones' => $sumaDocumentosRecepcion,
                    'SumaVolumenRecepcion' => [
                        'ValorNumerico' => $sumaRecepcionTanque,
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'TotalDocumentos' => $sumaDocumentosRecepcion,
                    'SumaCompras' => 0,
                    'Recepcion' => $recepcionesEB00
                ],
                'Entregas' => [
                    'TotalEntregas' => $totalSalidas,
                    'SumaVolumenEntregado' => [
                        'ValorNumerico' => round($sumaEntragadoCompleto,3),
                        'UnidadDeMedida' => 'UM03',
                    ],
                    'TotalDocumentos' => $sumaDocumentosSalida,
                    'SumaVentas' => 0,
                    'Entrega' => $salidasTotales
                ]
            ];

            array_push($ductoArray, $dataDucto);

            $fechaI = date('Y-m-d', strtotime($fechaBalance."-1 days"));
            $fechaI = $fechaI . " 05:00:00";
            $fechaF = $fechaBalance . " 05:30:00";

            $bitacoras = Bitacora::whereBetween('fecha_hora', [$fechaI, $fechaF])->get();
            $bitacoras->load('user');
            $bitacoras->load('tipoEvento');
            $bitacoraRegistros = [];

            foreach ($bitacoras as $bitacora) {
                $numRegistro++;
                $rowBitacora = [
                    "NumeroRegistro" => $numRegistro,
                    "FechaYHoraEvento" => $bitacora->fecha_hora,
                    "UsuarioResponsable" => $bitacora->usuario->usuario,
                    "TipoEvento" => $bitacora->evento->id,
                    "DescripcionEvento" => $bitacora->descripcion1 . $bitacora->descripcion2 . $bitacora->descripcion3,
                ];
                array_push($bitacoraRegistros, $rowBitacora);
            }

            $prodArray = [];
            
            $productoData = [
                'ClaveProducto' => $productoOmision->clave,
                'ComposDePropanoEnGasLP' => $propano->porcentajes->porcentaje,
                'ComposDeButanoEnGasLP' => $butano->porcentajes->porcentaje,
                'Ducto' => $ductoArray
            ];


            array_push($prodArray, $productoData);

            $descripcion_instalacion = $empresa->descripcion_instalacion;
            $numPermiso = $empresa->num_permiso;
            $dataExport = [
                'Version' => '1.0',
                'RfcContribuyente' => $empresa->rfc_contribuyente,
                'RfcRepresentanteLegal'=> $empresa->rfc_representante,
                'RfcProveedor'=> $empresa->proveedor,
                'Caracter' => $empresa->tipo_caracter,
                'ModalidadPermiso' => $empresa->modalidad_permiso,
                'NumPermiso' => $numPermiso,
                'ClaveInstalacion' => $empresa->clave_instalacion,
                'DescripcionInstalacion' => $descripcion_instalacion,
                'NumeroPozos' => 0,
                'NumeroTanques' => 0,
                'NumeroDuctosEntradaSalida' => 2,
                'NumeroDuctosTransporteDistribucion' => 0,
                'NumeroDispensarios' => '?',
                'FechaYHoraCorte' => $fechaHoraCorte,
                'Producto' => $prodArray,
                'Bitacora' => $bitacoraRegistros
                
            ];

            $permitted_chars = '0123456789ABCDEF';
            $hash1 = substr(str_shuffle($permitted_chars), 0,8);
            $hash2 = substr(str_shuffle($permitted_chars), 0,4);
            $hash3 = substr(str_shuffle($permitted_chars), 0,4);
            $hash4 = substr(str_shuffle($permitted_chars), 0,4);
            $hash5 = substr(str_shuffle($permitted_chars), 0,12);
            $numeroEnvio = '';
            switch (true) {
                case $balance->id < 10:
                    $numeroEnvio = 'ALM-000' . $balance->id;
                    break;
                case $balance->id < 100 && $balance->id > 9:
                    $numeroEnvio = 'ALM-00' . $balance->id;
                    break;
                case $balance->id < 1000 && $balance->id > 99:
                    $numeroEnvio = 'ALM-0' . $balance->id;
                    break;
                case $balance->id < 10000 && $balance->id > 999:
                    $numeroEnvio = 'ALM-' . $balance->id;
                    break;
            }
            $fileNameZip = "D_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5  . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $fechaBalance . "_" . $numeroEnvio . "_ALM_JSON.zip";
            $fileNameJson = "D_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5 . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $fechaBalance . "_" . $numeroEnvio . "_ALM_JSON.json";
            $fileNameXls = "D_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5 . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $fechaBalance . "_" . $numeroEnvio . "_ALM_JSON.xlsx";


            switch ($tipo) {
                case 'data':
                    $dataJson = [
                        'json' => $dataExport,
                    ];
                    return response()->json(
                        $dataJson
                        ,200);    
                    break;
                case 'export':
                    $dataJson = json_encode($dataExport, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    $path = '';
                    if ($unidad === 'litros') {
                        $path = 'descargables/litros/' . $fechaBalance . "/";  // Carpeta de archivo json
                    } else {
                        $path = 'descargables/tons/' . $fechaBalance . "/";  // Carpeta de archivo json
                    }
                    $pathJson = $path . $fileNameJson;  // path carpeta + archivo

                    # Poner la data en json
                    //Storage::disk('public')->put($pathJson, json_encode($dataJson, JSON_FORCE_OBJECT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                    Storage::disk('public')->put($pathJson, $dataJson);
                    
                    $pathToDownload = public_path(Storage::url($pathJson));
                    // Crear carpeta para el zip
                    $zipFolder = $path;
                    Storage::disk('public')->makeDirectory($zipFolder, $mode=0775);
                    
                    $zip_file = public_path(Storage::url($zipFolder)) . '/' . $fileNameZip;
                    // Inicializando la clase
                    $zip = new ZipArchive();
                    $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    $zip->addFile($pathToDownload, $fileNameJson);
                    $zip->close();
                    
                    $estadoFile = 2;

                    if ($unidad === 'litros') {
                        $archivosPrevios = Archivo::where('balance_id', $balance->id)->get();
                        $estadoFile = 1;
                        foreach ($archivosPrevios as $archivoInBD) {
                            $archivoInBD->estado = 2;
                            $archivoInBD->save();
                        }
                    }
                    $archivo = new Archivo();
                    $archivo->nombre = $fileNameJson;
                    $archivo->ruta = Storage::url($zipFolder) . $fileNameZip;
                    $archivo->tipo = 'j';
                    $archivo->usuario_id = $request->user()->id;
                    $archivo->balance_id = $balance->id;
                    $archivo->estado = $estadoFile;
                    $archivo->save();
                    $archivo->load('usuario');
                    $archivo->load('balance');
                    $bitacora = new Bitacora();
                    $bitacora->fecha = date('Y-m-d');
                    $bitacora->fecha_hora = date('Y-m-d H:i:s');
                    $bitacora->evento_id = 1;
                    $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
                    $bitacora->descripcion2 = 'generó el reporte excel ' . $archivo->id . ' del día ' . $balance->fecha;
                    $bitacora->descripcion3 = '';
                    $bitacora->usuario_id = $request->user()->id;
                    $bitacora->save();


                    return response()->json([
                        'data' => $archivo,
                        #'errores' => $erroresJson
                    ]);
                    break;
            }

        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->error('Error al generar json' . $th->getMessage());
        }
    }

    public function crearJsonMensualV1 (Request $request) {

        try {
            /*
                ** Función para generar archivo json de volumétricos **
            */

            /* Obtener datos de la Request */

            $idEmpresa = $request->idEmpresa;
            $fechaInicio = $request->fechaInicio;
            $fechaFinal = $request->fechaFinal;
            $tipo = $request->tipo;
            $unidad = $request->unidad;


            /* Obtener valores para cálculo */
            $fecha_inicio = strtotime(date($fechaInicio));
            $fecha_final = strtotime(date($fechaFinal));
            $periodo = date('Y-m', $fecha_inicio);
            $diaFinal = intval(date('d', $fecha_final));

            if ($fecha_inicio > $fecha_final) {
                $bitacora = new Bitacora();
                $bitacora->fecha = date('Y-m-d');
                $bitacora->fecha_hora = date('Y-m-d H:i:s');
                $bitacora->evento_id = 19;
                $bitacora->descripcion1 = 'Error en generación de archivo json, fechas de inicio mayor a la fecha final. ';
                $bitacora->descripcion2 = 'usuario: ' . $request->user()->usuario;
                $bitacora->descripcion3 = ' fecha inicio: ' . $fechaInicio . ' | fecha final: ' . $fechaFinal;
                $bitacora->usuario_id = $request->user()->id;
                $bitacora->save();
                $bitacora->load('usuario');
                $bitacora->load('evento');
                return response()->json([
                    'error' => 'La fecha de inicio no debe ser mayor a la fecha final.',
                    'data' => $bitacora
                ],501); 
            }

            // Obtener los datos Generales de la instalacion
            $empresa = Empresa::where('id', $idEmpresa)->first();
            // Obtener el número de tanques
            //$empresa->load('tanques');
            $empresa->load('productos');
            $empresa->load('productoOmision');

            $productoOmision = $empresa->productoOmision;
            $compuestos = $productoOmision->compuestos;
            $propano = $compuestos[0];
            $butano = $compuestos[1];

            $numRegistro = 0;

            $diasReporte = array();

            for ($i=1; $i <= $diaFinal; $i++) { 
                $diaForReporte = '';
                if ($i < 10 ) {
                    $diaForReporte = $periodo . '-0' . $i;
                } else {
                    $diaForReporte = $periodo . '-' . $i;
                }
                array_push($diasReporte, $diaForReporte);
            }

            $fechaHoraCorteDT = new DateTime($diasReporte[count($diasReporte) - 1] . " 05:00:00", new DateTimeZone('America/Mexico_City'));
            $fechaHoraCorte = $fechaHoraCorteDT->format(DateTime::ATOM);
            $mes = substr($fechaInicio, 5,2);
            $mesInt = intval($mes);

            $dataBalances = [];

            $numRecepciones = 0;
            $sumaRecepciones = 0;
            $numEntregas = 0;
            $sumaEntregas = 0;

            $complementosRec = [];
            $complementoSalida = [];

            foreach ($diasReporte as $fechaBalance) {
                $balance = Balance::where('fecha', $fechaBalance)->first();
                
                $balance->load('dictamenes');
                if (!is_null($balance)) {

                    if (count($balance->dictamenes) == 0) {
                        $bitacora = new Bitacora();
                        $bitacora->fecha = date('Y-m-d');
                        $bitacora->fecha_hora = date('Y-m-d H:i:s');
                        $bitacora->evento_id = 19;
                        $bitacora->descripcion1 = 'Error en generación de archivo json, falta algún dictamen. ';
                        $bitacora->descripcion2 = 'usuario: ' . $request->user()->usuario;
                        $bitacora->descripcion3 = '';
                        $bitacora->usuario_id = $request->user()->id;
                        $bitacora->save();
                        $bitacora->load('usuario');
                        $bitacora->load('evento');
    
                        return response()->json([
                            'error' => 'Falta dictamen del día: '. $balance->fecha . '  para crear el archivo json.',
                            'data' => $bitacora
                        ],501); 
                    }

                    /* Obtener las recepciones */
                    $recepcionGas = DB::table('balances_duca.entradas')
                        ->select(DB::raw('MAX(valor) - MIN(valor) AS recibido'))
                        ->whereRaw('balance_id = ?', [$balance->id])
                        ->first();
                    
                    

                    if (count($balance->dictamenes) > 0) {
                        $dictamen = $balance->dictamenes[0];
                        $dictamen->load('cliente');
                    }

                    $fechaFolioDictamen = date('Y', strtotime($fechaBalance));
                    $numFolioDictamen = '0';
                    
                    switch (true) {
                        case $dictamen->id < 10:
                            $numFolioDictamen = "0000".$dictamen->id;
                            break;
                        case $dictamen->id >= 10 && $dictamen->id < 100:
                            $numFolioDictamen = "000".$dictamen->id;
                            break;
                        case $dictamen->id >= 100 && $dictamen->id < 1000:
                            $numFolioDictamen = "00".$dictamen->id;
                            break;
                        case $dictamen->id >= 1000 && $dictamen->id < 10000:
                            $numFolioDictamen = "0".$dictamen->id;
                            break;
                    }
                    if (str_contains($dictamen->folioDictamen, 'TEMP')) {
                        $folioDictamen = $dictamen->folioDictamen;
                    } else {
                        $folioDictamen = $dictamen->rfcDictamen . $numFolioDictamen . $fechaFolioDictamen;
                    }
                    //dd($recepcionGas->recibido);
                    if ($recepcionGas->recibido > 0) {
                        /*
                        echo "sumaRecepciones => " . $sumaRecepciones;
                        echo '<br />';
                        echo "sumaRecepciones + recepcionGas->recibido => " . $sumaRecepciones;
                        echo '<br />';
                        */
                        $sumaRecepciones = $sumaRecepciones + $recepcionGas->recibido;
                        
                        $rowComplemento = [
                            'TipoComplemento' => 'Almacenamiento',
                            'Dictamen' => [
                                'RfcDictamen' => $dictamen->rfcDictamen,
                                'LoteDictamen' => $dictamen->loteDictamen,
                                'NumeroFolioDictamen' => $folioDictamen,
                                'FechaEmisionDictamen' => $dictamen->fechaEmisionDictamen,
                                'ResultadoDictamen' => $dictamen->resultadoDictamen,
                            ],
                            'Nacional' => [
                                'RfcClienteOProveedor' => $dictamen->cliente->rfcCliente,
                                'NombreClienteOProveedor' => $dictamen->cliente->nombreCliente,
                            ]
                        ];
                        array_push($complementosRec,$rowComplemento);
                        $numRecepciones++;
                    }

                    /* Obtener las entregas */

                    # Entregas Llendaderas
                    $salidas = Salida::where('balance_id', $balance->id)->where('tipo', 'l')->orderBy('id', 'asc')->get();
                    $salidas->load('compania');
                    
                    foreach ($salidas as $salida) {
                        if ($salida->cliente == 4) {
                            $companiaSalida = Cliente::where('id', 1)->first();
                            //dd($companiaSalida);
                        } else {
                            $companiaSalida = $salida->compania;
                        }
                        $sumaEntregas = $sumaEntregas + $salida->valor;
                        $rowComplementoLlenadera = [
                            'TipoComplemento' => 'Almacenamiento',
                            'Dictamen' => [
                                'RfcDictamen' => $dictamen->rfcDictamen,
                                'LoteDictamen' => $dictamen->loteDictamen,
                                'NumeroFolioDictamen' => $folioDictamen,
                                'FechaEmisionDictamen' => $dictamen->fechaEmisionDictamen,
                                'ResultadoDictamen' => $dictamen->resultadoDictamen,
                            ],
                            'Nacional' => [
                                'RfcClienteOProveedor' => $companiaSalida->rfcCliente,
                                'NombreClienteOProveedor' => $companiaSalida->rfcCliente,
                            ]
                        ];
                        //dd($rowComplementoLlenadera);
                        array_push($complementoSalida,$rowComplementoLlenadera);
                        $numEntregas++;
                        
                    }

                    
                } else {
                    echo 'No se encontró Balance: ' . $fechaBalance . '<br />';
                }

            }

            $ultimoDia = $diasReporte[count($diasReporte) - 1 ];
            $ultimoBalance = Balance::where('fecha', $ultimoDia)->first();
            $almacenamientoFinal = $ultimoBalance->almacenamiento;
            $bitacora = new Bitacora();
            $bitacora->fecha = date('Y-m-d');
            $bitacora->fecha_hora = date('Y-m-d H:i:s');
            $bitacora->evento_id = 19;
            $bitacora->descripcion1 = 'Generación de archivo json ';
            $bitacora->descripcion2 = 'usuario: ' . $request->user()->usuario;
            $bitacora->descripcion3 = ' fecha: ' . $fechaBalance;
            $bitacora->usuario_id = 1;
            $bitacora->save();

            $fechaFF = $fechaFinal . " 23:59:59";
            $bitacoras = Bitacora::whereBetween('fecha_hora', [$fechaInicio, $fechaFF])->get();
            $bitacoras->load('user');
            $bitacoras->load('tipoEvento');
            $bitacoraRegistros = [];

            foreach ($bitacoras as $bitacora) {
                $numRegistro++;
                $rowBitacora = [
                    "NumeroRegistro" => $numRegistro,
                    "FechaYHoraEvento" => $bitacora->fecha_hora,
                    "UsuarioResponsable" => $bitacora->user->usuario,
                    "TipoEvento" => $bitacora->tipoEvento->id,
                    "DescripcionEvento" => $bitacora->descripcion1 . $bitacora->descripcion2 . $bitacora->descripcion3,
                ];
                array_push($bitacoraRegistros, $rowBitacora);
            }

            $prodArray = [];
            $productoData = [
                'ClaveProducto' => $productoOmision->clave,
                'GasLP' => [
                    'ComposDePropanoEnGasLP' => $propano->porcentajes->porcentaje,
                    'ComposDeButanoEnGasLP' => $butano->porcentajes->porcentaje,
                ],
                'MarcaComercial' => '?',
                'Marcaje' => '?',
                'ConcentracionSustanciaMarcaje' => '?',
                'ReporteDeVolumenMensual' => [
                    'ControlDeExistencias' => [
                        'VolumenExistenciasMes' => round($this->convertLitros($unidad, $almacenamientoFinal, $dictamen->densidad),3),
                        'FechaYHoraEstaMedicionMes' => $fechaHoraCorte
                    ],
                    'Recepciones' => [
                        'TotalRecepcionesMes' => $numRecepciones,
                        'SumaVolumenRecepcionMes' => [
                            'ValorNumerico' => round($this->convertLitros($unidad, $sumaRecepciones, $dictamen->densidad),3),
                            'UnidadDeMedida' => 'UM03'
                        ],
                        'TotalDocumentosMes' => $numRecepciones,
                        'ImporteTotalRecepcionesMensual' => 0,
                        'Complemento' => $complementosRec
                    ],
                    'Entregas' => [
                        'TotalEntregasMes' => $numEntregas,
                        'SumaVolumenEntregadoMes' => [
                            'UnidadDeMedida' => 'UM03',
                            'ValorNumerico' => round($this->convertLitros($unidad, $sumaEntregas, $dictamen->densidad),3),
                        ],
                        'TotalDocumentosMes' => $numEntregas,
                        'ImporteTotalEntregasMes' => 0,
                        'Complemento' => $complementoSalida
                    ]
                ]
            ];

            array_push($prodArray, $productoData);

            $caracterArray = [
                'TipoCaracter' => $empresa->tipo_caracter,
                'ModalidadPermiso' => $empresa->modalidad_permiso,
                'NumPermiso' => $empresa->num_permiso,
                'NumContratoOAsignacion' => 'Falta este dato',
                'InstalacionAlmacenGasNatural' => 'Aqui va un texto de la empresa.'
            ];

            $geolicalizacionArray = [
                'GeolocalizacionLatitud' => 21.8041458,
                'GeolocalizacionLongitud' =>  -104.8409271
            ];

            $dataExport = [
        
                    'Version' => '1.0',
                    'RfcContribuyente' => $empresa->rfc_contribuyente,
                    'RfcRepresentanteLegal'=> $empresa->rfc_representante,
                    'RfcProveedor'=> $empresa->proveedor,
                    'Caracter' => $caracterArray,
                    'ClaveInstalacion' => $empresa->clave_instalacion,
                    'DescripcionInstalacion' => $empresa->descripcion_instalacion,
                    'Geolocalizacion' => $geolicalizacionArray,
                    'NumeroPozos' => 0,
                    'NumeroTanques' => 0,
                    'NumeroDuctosEntradaSalida' => '11 ¿10 llenaderas y 1 ducto?',
                    'NumeroDuctosTransporteDistribucion' => '1 ¿Debe ir 1 ó 0?',
                    'NumeroDispensarios' => '10 ¿Deben ser las llenaderas?',
                    'FechaYHoraReporteMes' => $fechaHoraCorte,
                    'Producto' => $prodArray,
                    'BitacoraMensual' => $bitacoraRegistros
                
            ];

            //  D ó M => Diario o Mensual / RFC Empresa / RFC Repre Legal / Fecha / ClaveInstalacion _ALM_JSON.zip
            $permitted_chars = '0123456789ABCDEF';
            $hash1 = substr(str_shuffle($permitted_chars), 0,8);
            $hash2 = substr(str_shuffle($permitted_chars), 0,4);
            $hash3 = substr(str_shuffle($permitted_chars), 0,4);
            $hash4 = substr(str_shuffle($permitted_chars), 0,4);
            $hash5 = substr(str_shuffle($permitted_chars), 0,12);
            $mesStr = $mesInt < 10 ? 'ALM-000' . $mesInt : 'ALM-00' . $mesInt;
            $penultimoDia = $diasReporte[count($diasReporte) - 2 ];

            $fileNameZip = "M_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5  . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $penultimoDia. "_" . $mesStr . "_ALM_JSON.zip";
            $fileNameJson = "M_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5 . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $penultimoDia. "_" . $mesStr . "_ALM_JSON.json";
            $fileNameXls = "M_". $hash1. "-" . $hash2 . "-" . $hash3. "-" . $hash4 . "-" . $hash5 . "_" . $empresa->rfc_contribuyente . "_" . $empresa->proveedor . "_" . $penultimoDia. "_" . $mesStr . "_ALM_JSON.xlsx";

            switch ($tipo) {
                case 'data':
                    return response()->json(
                        $dataExport,200);
                    break;
                case 'export':
                    $dataJson = json_encode($dataExport, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                    //dd($dataJson);
                    
                    $path = '';
                    $estadoFile = 2;
                    if ($unidad === 'litros') {
                        $path = 'descargables/litros/' . $mesStr . "/";  // Carpeta de archivo json
                        $estadoFile = 1;
                        #Revisar que no haya archivos anteriores
                        $archivosPrevios = ArchivoMensual::where('periodo', $periodo)->get();
                        foreach ($archivosPrevios as $archivoInBD) {
                            $archivoInBD->estado = 2;
                            $archivoInBD->save();
                        }
                    } else {
                        $path = 'descargables/tons/' . $mesStr . "/";  // Carpeta de archivo json
                    }
                    
                    $pathJson = $path . $fileNameJson;  // path carpeta + archivo
        
                    # Poner la data en json
                    Storage::disk('public')->put($pathJson, $dataJson);
        
                    $pathToDownload = public_path(Storage::url($pathJson));
                    // Crear carpeta para el zip
                    $zipFolder = $path;
                    Storage::disk('public')->makeDirectory($zipFolder, $mode=0775);
                    
                    $zip_file = public_path(Storage::url($zipFolder)) . '/' . $fileNameZip;
                    // Inicializando la clase
                    $zip = new ZipArchive();
                    $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    $zip->addFile($pathToDownload, $fileNameJson);
                    $zip->close();
        
                    $archivo = new ArchivoMensual();
                    $archivo->nombre = $fileNameJson;
                    $archivo->ruta = Storage::url($zipFolder) . $fileNameZip;
                    $archivo->usuario_id = $request->user()->id;
                    $archivo->periodo = $periodo;
                    $archivo->estado = $estadoFile;
                    $archivo->save();
                    $archivo->load('usuario');
        
                    $bitacora = new Bitacora();
                    $bitacora->fecha = date('Y-m-d');
                    $bitacora->fecha_hora = date('Y-m-d H:i:s');
                    $bitacora->evento_id = 1;
                    $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
                    $bitacora->descripcion2 = 'generó el reporte json mensual  ' . $archivo->id . ' del periodo ' . $periodo;
                    $bitacora->descripcion3 = '';
                    $bitacora->usuario_id = $request->user()->id;
                    $bitacora->save();
        
                    return response()->json([
                        'data' => $archivo
                    ]);
                    break;
                case 'excel':
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    //dd($dataExport);
                    $row = 1;
                    foreach ($dataExport as $key => $value) {
                        $titulo = 'A'.$row;
                        $valor = 'B'.$row;
                        //dd($valor);
                        if ($row < 16) {
                            $sheet->setCellValue($titulo, $key);
                            $sheet->setCellValue($valor, $value);
                            $row++;
                        } else {
                            break;
                        }
                    }
                    $jsProducto = $dataExport['Producto'][0];
                    //dd($jsProducto);
                    $sheet->setCellValue('A16', 'Producto');
                    $sheet->setCellValue('B17', 'ClaveProducto');
                    $sheet->setCellValue('C17', $jsProducto['ClaveProducto']);
                    $sheet->setCellValue('B18', 'ComposDePropanoEnGasLP');
                    $sheet->setCellValue('C18', $jsProducto['ComposDePropanoEnGasLP']);
                    $sheet->setCellValue('B19', 'ComposDeButanoEnGasLP');
                    $sheet->setCellValue('C19', $jsProducto['ComposDeButanoEnGasLP']);
                    $sheet->setCellValue('B20', 'ReporteDeVolumenMensual');
                    $sheet->setCellValue('C21', 'ControlDeExistencias');
                    $sheet->setCellValue('D22', 'VolumenExistenciasMes');
                    $sheet->setCellValue('E22', $jsProducto['ReporteDeVolumenMensual']['ControlDeExistencias']['VolumenExistenciasMes']);
                    $sheet->setCellValue('D23', 'FechaYHoraEstaMedicionMes');
                    $sheet->setCellValue('E23', $jsProducto['ReporteDeVolumenMensual']['ControlDeExistencias']['FechaYHoraEstaMedicionMes']);
                    $sheet->setCellValue('C24', 'Recepciones');
                    $sheet->setCellValue('D25', 'TotalRecepcionesMes');
                    $sheet->setCellValue('E25', $jsProducto['ReporteDeVolumenMensual']['Recepciones']['TotalRecepcionesMes']);
                    $sheet->setCellValue('D26', 'SumaVolumenRecepcionMes');
                    $sheet->setCellValue('E27', 'ValorNumerico');
                    $sheet->setCellValue('F27', $jsProducto['ReporteDeVolumenMensual']['Recepciones']['SumaVolumenRecepcionMes']['ValorNumerico']);
                    $sheet->setCellValue('E28', 'UnidadDeMedida');
                    $sheet->setCellValue('F28', $jsProducto['ReporteDeVolumenMensual']['Recepciones']['SumaVolumenRecepcionMes']['UnidadDeMedida']);
                    $sheet->setCellValue('D29', 'TotalDocumentosMes');
                    $sheet->setCellValue('E29', $jsProducto['ReporteDeVolumenMensual']['Recepciones']['TotalDocumentosMes']);
                    $sheet->setCellValue('D29', 'ImporteTotalRecepcionesMensual');
                    $sheet->setCellValue('E29', $jsProducto['ReporteDeVolumenMensual']['Recepciones']['ImporteTotalRecepcionesMensual']);
                    $sheet->setCellValue('D30', 'Complemento');
                    
                    $row = 31;
                    $complementosRec = $jsProducto['ReporteDeVolumenMensual']['Recepciones']['Complemento'];
                    //dd($complementosRec);

                    foreach ($complementosRec as $complemento) {
                        $sheet->setCellValue('E'.$row, 'TipoComplemento');
                        $sheet->setCellValue('F'.$row, $complemento['TipoComplemento']);
                        $row++;
                        $sheet->setCellValue('E'.$row, 'Dictamen');
                        $row++;
                        $sheet->setCellValue('F'.$row, 'RfcDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['RfcDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'LoteDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['LoteDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'NumeroFolioDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['NumeroFolioDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'FechaEmisionDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['FechaEmisionDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'ResultadoDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['ResultadoDictamen']);
                        $row++;
                        $sheet->setCellValue('E'.$row, 'Nacional');
                        $row++;
                        $sheet->setCellValue('F'.$row, 'RfcClienteOProveedor');
                        $sheet->setCellValue('G'.$row, $complemento['Nacional']['RfcClienteOProveedor']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'NombreClienteOProveedor');
                        $sheet->setCellValue('G'.$row, $complemento['Nacional']['NombreClienteOProveedor']);
                        $row++;
                    }

                    $sheet->setCellValue('C'.$row, 'Entregas');
                    $row++;
                    $sheet->setCellValue('D'.$row, 'TotalEntregasMes');
                    $sheet->setCellValue('E'.$row, $jsProducto['ReporteDeVolumenMensual']['Recepciones']['TotalRecepcionesMes']);
                    $row++;
                    $sheet->setCellValue('D'.$row, 'SumaVolumenEntregadoMes');
                    $row++;
                    $sheet->setCellValue('E'.$row, 'ValorNumerico');
                    $sheet->setCellValue('F'.$row, $jsProducto['ReporteDeVolumenMensual']['Recepciones']['SumaVolumenRecepcionMes']['ValorNumerico']);
                    $row++;
                    $sheet->setCellValue('E'.$row, 'UnidadDeMedida');
                    $sheet->setCellValue('F'.$row, $jsProducto['ReporteDeVolumenMensual']['Recepciones']['SumaVolumenRecepcionMes']['UnidadDeMedida']);
                    $row++;
                    $sheet->setCellValue('D'.$row, 'TotalDocumentosMes');
                    $sheet->setCellValue('E'.$row, $jsProducto['ReporteDeVolumenMensual']['Recepciones']['TotalDocumentosMes']);
                    $row++;
                    $sheet->setCellValue('D'.$row, 'ImporteTotalEntregasMes');
                    $sheet->setCellValue('E'.$row, $jsProducto['ReporteDeVolumenMensual']['Recepciones']['ImporteTotalRecepcionesMensual']);
                    $row++;
                    $sheet->setCellValue('D'.$row, 'Complemento');
                    $row++;

                    $complementosEnt = $jsProducto['ReporteDeVolumenMensual']['Entregas']['Complemento'];
                    //dd($complementosEnt);

                    foreach ($complementosEnt as $complemento) {
                        //dd($complemento);
                        $sheet->setCellValue('E'.$row, 'TipoComplemento');
                        $sheet->setCellValue('F'.$row, $complemento['TipoComplemento']);
                        $row++;
                        $sheet->setCellValue('E'.$row, 'Dictamen');
                        $row++;
                        $sheet->setCellValue('F'.$row, 'RfcDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['RfcDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'LoteDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['LoteDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'NumeroFolioDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['NumeroFolioDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'FechaEmisionDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['FechaEmisionDictamen']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'ResultadoDictamen');
                        $sheet->setCellValue('G'.$row, $complemento['Dictamen']['ResultadoDictamen']);
                        $row++;
                        $sheet->setCellValue('E'.$row, 'Nacional');
                        $row++;
                        $sheet->setCellValue('F'.$row, 'RfcClienteOProveedor');
                        $sheet->setCellValue('G'.$row, $complemento['Nacional']['RfcClienteOProveedor']);
                        $row++;
                        $sheet->setCellValue('F'.$row, 'NombreClienteOProveedor');
                        $sheet->setCellValue('G'.$row, $complemento['Nacional']['NombreClienteOProveedor']);
                        $row++;
                    }

                    $rowBitacora = $row;
                    $sheet->setCellValue('A'.$rowBitacora, 'BitacoraMensual');
                    $rowBitacora++;
                    $registros = $dataExport['BitacoraMensual'];    // Registros de bitácora mensual

                    foreach ($registros as $registro) {
                        //dd($registro);
                        $sheet->setCellValue('B'.$rowBitacora, 'NumeroRegistro');
                        $sheet->setCellValue('C'.$rowBitacora, $registro['NumeroRegistro']);
                        $rowBitacora++;
                        $sheet->setCellValue('B'.$rowBitacora, 'FechaYHoraEvento');
                        $sheet->setCellValue('C'.$rowBitacora, $registro['FechaYHoraEvento']);
                        $rowBitacora++;
                        $sheet->setCellValue('B'.$rowBitacora, 'UsuarioResponsable');
                        $sheet->setCellValue('C'.$rowBitacora, $registro['UsuarioResponsable']);
                        $rowBitacora++;
                        $sheet->setCellValue('B'.$rowBitacora, 'TipoEvento');
                        $sheet->setCellValue('C'.$rowBitacora, $registro['TipoEvento']);
                        $rowBitacora++;
                        $sheet->setCellValue('B'.$rowBitacora, 'DescripcionEvento');
                        $sheet->setCellValue('C'.$rowBitacora, $registro['DescripcionEvento']);
                        $rowBitacora++;
                    }

                    $pathE = '';
                    if ($unidad === 'litros') {
                        $pathE = 'descargables/litros/excel/' . $mesStr . "/";  // Carpeta de archivo json
                    } else {
                        $pathE = 'descargables/tons/excel/' . $mesStr . "/";  // Carpeta de archivo json
                    }
                    $pathExcel = $pathE . $fileNameXls;  // path carpeta + archivo
                    
                    $writer = new Xlsx($spreadsheet);
                    ob_start();
                    $writer->save('php://output');
                    $content = ob_get_contents();
                    ob_end_clean();

                    # Poner la data en json
                    //Storage::disk('public')->put($pathJson, json_encode($dataJson, JSON_FORCE_OBJECT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                    Storage::disk('public')->put($pathExcel, $content);

                    $pathToDownloadE = public_path(Storage::url($pathExcel));
                    // Crear carpeta para el zip
                    $zipFolderE = $pathE;
                    Storage::disk('public')->makeDirectory($zipFolderE, $mode=0775);
                    
                    $zip_fileE = public_path(Storage::url($zipFolderE)) . '/' . $fileNameZip;
                    // Inicializando la clase
                    $zip = new ZipArchive();
                    $zip->open($zip_fileE, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                    $zip->addFile($pathToDownloadE, $fileNameXls);
                    $zip->close();

                    $archivo = new ArchivoMensual();
                    $archivo->nombre = $fileNameXls;
                    $archivo->ruta = Storage::url($zipFolderE) . $fileNameZip;
                    $archivo->usuario_id = $request->user()->id;
                    $archivo->periodo = $periodo;
                    $archivo->estado = 2;
                    $archivo->save();
                    $archivo->load('usuario');

                    $bitacora = new Bitacora();
                    $bitacora->fecha = date('Y-m-d');
                    $bitacora->fecha_hora = date('Y-m-d H:i:s');
                    $bitacora->evento_id = 1;
                    $bitacora->descripcion1 = 'El usuario ' . $request->user()->usuario;
                    $bitacora->descripcion2 = 'generó el reporte excel ' . $archivo->id . ' del día ' . $balance->fecha;
                    $bitacora->descripcion3 = '';
                    $bitacora->usuario_id = $request->user()->id;
                    $bitacora->save();

                    //return response()->download($zip_file);
                    return response()->json([
                        'data' => $archivo
                    ]);


                    break;
            }

        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->error('Error al generar json' . $th->getMessage());
        }
    }

    private function convertLitros($tipo, $valor, $densidad)
    {   
        return $tipo === 'tons' ? $valor : ($valor * 1000) / $densidad;
    }

    public function checkDataExperion($fecha) {
        try {
            // Revisar si se subieron los datos del seleccionado
            
            //  balance diario
            $balance = Balance::where('fecha', $fecha)->first();

            $faltantes = array();
            if ($balance == NULL)
            {
                return response()->json([
                    'estatus' => false,
                    'data' => 'No se encontró el balance con la fecha ' . $fecha
                ],200);
            }

            $salidasLlenaderas = Salida::where('balance_id', $balance->id_balance)->where('tipo', 'l')->get();
            if (count($salidasLlenaderas) == 0) {
                array_push($faltantes, 'Salidas Llenadera');
            }

            /* //  contenedorMovimientos
            $contenedorMovimientos = ContenedorMovimiento::where('balance_id', $balance->id_balance)->get();
            if (count($contenedorMovimientos) == 0) {
                array_push($faltantes, 'Movimientos de Esferas');
            }

            //  balanceMovimientos
            $balanceMovimientos = BalanceMovimiento::where('balance_id', $balance->id_balance)->get();
            if (count($balanceMovimientos) == 0) {
                array_push($faltantes, 'Entradas - Salidas Procesadas Esferas');
            }

            //  contenedor Balance
            $contenedorBalance = ContenedorBalance::where('balance_id', $balance->id_balance)->get();
            if (count($contenedorBalance) == 0) {
                array_push($faltantes, 'Inventario Final de Esferas');
            } */

            if($balance->almacenamiento == 0 && $balance->inventarioInicial == 0) {
                array_push($faltantes, 'Balance Actualizado');
            }

            $estatus = count($faltantes) == 0 ? true : false;
            $mensaje = count($faltantes) == 0 ? 'Información Completa' : $faltantes;

            return response()->json([
                'estatus' => $estatus,
                'data' => $mensaje
            ],200);
            

        } catch (\Throwable $th) {
            return $this->error("Error al eliminar el registro, error:{$th->getMessage()}.");
        }
    }

}
