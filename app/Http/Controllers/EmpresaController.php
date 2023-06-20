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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponder;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    use ApiResponder;

    public function index()
    {
        $empresas = Empresa::all();
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
                return response(['errors' => $validator->errors()->all()], 422);
            }
            
            $empresa = new Empresa($request->all());
            $empresa->save();

            return response()->json([
                'data' => $empresa
            ],201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function show($idEmpresa)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();

            if ($empresa == NULL)
            {
                return response()->json([
                    'data' => 'No se encontró la empresa seleccionada.'
                ],202);
            }
            $empresa->load('tanques');
            $empresa->load('productos');

            return response()->json([
                'data' => $empresa
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
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
                return response(['errors' => $validator->errors()->all()], 422);
            }

            $empresa = Empresa::where('id', $idEmpresa)->first();
            if ($empresa == NULL)
            {
                return response()->json([
                    'data' => 'No se encontró la empresa seleccionada.'
                ],202);
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

            return response()->json([
                'data' => $empresa
            ],202);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function destroy($idEmpresa, Request $request)
    {
        try {
            $empresa = Empresa::where('id', $idEmpresa)->first();
            $empresa->delete();

            return response()->json([
                'data' => $empresa
            ],202);
            
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 501);
        }
    }

    public function crearJsonV1 ($idEmpresa, $fechaBalance, $tipo, $unidad, Request $request) {

        try {
            /*
                ** Función para generar archivo json de volumétricos **
            */

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

            if (count($dictamenes) < 1) {
                $bitacora = new Bitacora();
                $bitacora->fecha = date('Y-m-d');
                $bitacora->fecha_hora = date('Y-m-d H:i:s');
                $bitacora->tipoevento_id = 19;
                $bitacora->descripcion1 = 'Error en generación de archivo json, no hay dictamen selecionado. ';
                $bitacora->descripcion2 = 'usuario: ' . $request->user()->usuario;
                $bitacora->descripcion3 = 'fecha ' . $fechaBalance;
                $bitacora->usuario_id = $request->user()->id;
                $bitacora->save();
                $bitacora->load('user');
                $bitacora->load('evento');
                $this->error("No se han creado dictámenes para el día " . $fechaBalance);
            }

            if (count($dictamenes) == 2) {

                $recepcionGas = DB::table('balances_duca.entradas')
                    ->select(DB::raw('MAX(valor) - MIN(valor) AS recibido'))
                    ->whereRaw('balance_id = ?', [$balance->id])
                    ->first();
                $volRecibido = is_null($recepcionGas->recibido) ? 0 : $recepcionGas->recibido;
                foreach ($dictamenes as $dic) {
                    $dic->load('cliente');
                    dd($dic);
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

                $recepcionGas = DB::table('balances_duca.entradas')
                    ->select(DB::raw('MAX(valor) - MIN(valor) AS recibido'))
                    ->whereRaw('balance_id = ?', [$balance->id])
                    ->first();

                # Checar la cantidad de gas recibido  
                $volRecibido = is_null($recepcionGas->recibido) ? 0 : $recepcionGas->recibido;
                

                $entregaLlenadera = DB::table('balances_duca.salidas')
                    ->select(DB::raw('SUM(valor) AS recibido'))
                    ->whereRaw("balance_id = ? AND tipo = ?", [$balance->id,'l'])
                    ->first();
                #dd($entregaLlenadera);

                $balance->load('recibos');
                $numRecibos = count($balance->recibos);

                $reciboTot = 0;
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
                }

                # Checar la cantidad de gas recibido  
                $volEntregadoLlenadera = is_null($entregaLlenadera->recibido) ? 0 : $entregaLlenadera->recibido;
                #dd($volEntregadoLlenadera);
                $volEntregado = $volEntregadoLlenadera;

                $restanteEntradaEB00 = 0;
                $totalEntradaEB00 = 0;

                $entradas = [];
                $totalEntradasEB00 = 0; // $totalEntradasEntregas
                $inventarioInicial = 0;

                /* Obtener las recepciones */
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

                foreach ($entradas as $entrada) {
                    $totalEntradasEB00 = $totalEntradasEB00 + floatval($entrada['valor']);
                }

                /** 
                 * $registrosEntradas -> Entradas IRGE <-
                */
                
                $recepcionesEB00 = []; // almacena los registros json del muelle
                $totalEntradaEB00 = $volRecibido; // Total de recibido del muelle
                $restanteEntradaEB00 = $volRecibido; // Total de entregado en el muelle
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
                    
                    #$registroAlmTanqueAnterior = ContenedorBalance::where('balance_id', '<', $balance->id_balance)->where('contenedor_id', $idTanque)->orderBy('balance_id', 'desc')->first();
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
                                array_push($rowRecepcion, $dataRecepcion);
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
                    $recepcionTanque = [
                        /* "tanque_id" => $idTanque, */
                        "recepcion" => $rowRecepcion,
                        "inventarioFinal" => $restanteAlmTanqueRec
                    ];
                    array_push($recepcionesEB00, $recepcionTanque);
                }


                /** 
                 * $registrosVentas -> Salidas IRGE <-
                */
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
                $restanteEntradaEB00 = $volRecibido;
                
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
                                'VolumenInicialTanque' => [
                                    'ValorNumerico' => round($this->convertLitros($unidad, $inventarioInicialSal, $salidas[$i]->densidad),3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'VolumenFinalTanque' => round($this->convertLitros($unidad, ($inventarioInicialSal - $masa), $salidas[$i]->densidad),3),
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
                            array_push($rowSalida, $dataEntrega);
                        }
                    }
                    
                }
                
                $inventarioInicialConv = $this->convertLitros($unidad, $inventarioInicial, $densidadBalance);

                $medidoresArray = [];
                $medidoresRow = [
                    /* 'SistemaMedicionTanque' => $tanque->sistema_medicion,
                    'LocalizODescripSistMedicionTanque' => $tanque->localizcion_sistema_medicion,
                    'VigenciaCalibracionSistMedicionTanque' => $tanque->vigencia_calibracion_sistema_medicion,
                    'IncertidumbreMedicionSistMedicionTanque' => $tanque->incertidumbre_sistema_medicion */
                    'SistemaMedicionTanque' => 'duda',
                    'LocalizODescripSistMedicionTanque' => 'duda',
                    'VigenciaCalibracionSistMedicionTanque' => 'duda',
                    'IncertidumbreMedicionSistMedicionTanque' => 'duda'
                ];
                array_push($medidoresArray, $medidoresRow);

                $existenciaFinal = $inventarioInicialConv + $sumaRecepcionTanque - $sumaEntragadoCompleto;
                $tanquesArray = [];

                $dataTanque = [
                    'ClaveIdentificacionTanque' => 'Duda',
                    'Localizaciony/oDescripcionTanque' => 'Duda',
                    'VigenciaCalibracionTanque' => 'Duda',
                    'CapacidadTotalTanque' => [
                        'ValorNumerico' => 'Duda',
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'CapacidadOperativaTanque' => [
                        'ValorNumerico' => 'Duda',
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'CapacidadUtilTanque' => [
                        'ValorNumerico' => 'Duda',
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'CapacidadFondajeTanque' => [
                        'ValorNumerico' => 'Duda',
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'VolumenMinimoOperacion' => [
                        'ValorNumerico' => 'Duda',
                        'UnidadDeMedida' => 'UM03'
                    ],
                    'EstadoTanque' => 'Duda',
                    'Medidores' => $medidoresArray,
                    // Obtener las existencias
                    
                    'Existencias' => [
                        'VolumenExistenciasAnterior' => round($inventarioInicialConv,3),
                        'VolumenAcumOpsRecepcion' => [
                            'ValorNumerico' => $sumaRecepcionTanque,
                            'UnidadDeMedida' => 'UM03'
                        ],
                        'HoraRecepcionAcumulado' => $horaCorte,
                        'VolumenAcumOpsEntrega' => [
                            'ValorNumerico' => round($sumaEntragadoCompleto,3),
                            'UnidadDeMedida' => 'UM03'
                        ],
                        'HoraEntregaAcumulado' => $horaCorte,
                        'VolumenExistencias' => round(($inventarioInicialConv + $sumaRecepcionTanque - $sumaEntragadoCompleto),3),
                        'FechaYHoraEstaMedicion' => $fechaHoraCorte,
                        'FechaYHoraMedicionAnterior' => $fechaHoraCorteAnt
                    ],
                    'Recepciones' => [
                        'TotalRecepciones' => $sumaDocumentosRecepcion,
                        'SumaVolumenRecepcion' => [
                            'ValorNumerico' => $sumaRecepcionTanque,
                            'UnidadDeMedida' => 'UM03'
                        ],
                        'TotalDocumentos' => $sumaDocumentosRecepcion,
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
                    ]
                ];

                array_push($tanquesArray, $dataTanque);

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
                        "UsuarioResponsable" => $bitacora->user->usuario,
                        "TipoEvento" => $bitacora->tipoEvento->id,
                        "DescripcionEvento" => $bitacora->descripcion1 . $bitacora->descripcion2 . $bitacora->descripcion3,
                    ];
                    array_push($bitacoraRegistros, $rowBitacora);
                }

                $prodArray = [];
                $productoData = [
                    'ClaveProducto' => $productoOmision->clave,
                    'ComposDePropanoEnGasLP' => $propano->porcentaje->porcentaje,
                    'ComposDeButanoEnGasLP' => $butano->porcentaje->porcentaje,
                    'Tanque' => $tanquesArray
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
                    'NumeroDuctosEntradaSalida' => 14,
                    'NumeroDuctosTransporteDistribucion' => 0,
                    'NumeroDispensarios' => 0,
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
                    case $balance->id_balance < 10:
                        $numeroEnvio = 'ALM-000' . $balance->id_balance;
                        break;
                    case $balance->id_balance < 100 && $balance->id_balance > 9:
                        $numeroEnvio = 'ALM-00' . $balance->id_balance;
                        break;
                    case $balance->id_balance < 1000 && $balance->id_balance > 99:
                        $numeroEnvio = 'ALM-0' . $balance->id_balance;
                        break;
                    case $balance->id_balance < 10000 && $balance->id_balance > 999:
                        $numeroEnvio = 'ALM-' . $balance->id_balance;
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
                            $archivosPrevios = Archivo::where('balance_id', $balance->id_balance)->get();
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
                        $archivo->balance_id = $balance->id_balance;
                        $archivo->empresa_id = $empresa->id;
                        $archivo->estado = $estadoFile;
                        $archivo->save();
                        $archivo->load('usuario');
                        $archivo->load('balance');
                        $bitacora = new Bitacora();
                        $bitacora->fecha = date('Y-m-d');
                        $bitacora->fecha_hora = date('Y-m-d H:i:s');
                        $bitacora->tipoevento_id = 1;
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
                


                /* if ($sumaDocumentosRecepcion > 0) { 
                    if ($sumaDocumentosSalida > 0) {
                        $dataTanque = [
                            'ClaveIdentificacionTanque' => $tanque->clave,
                            'Localizaciony/oDescripcionTanque' => $tanque->localizacion_descripcion,
                            'VigenciaCalibracionTanque' => $tanque->vigencia_calibracion,
                            'CapacidadTotalTanque' => [
                                'ValorNumerico' => $tanque->capacidad_total,
                                'UnidadDeMedida' => 'UM03'
                            ],
                            'CapacidadOperativaTanque' => [
                                'ValorNumerico' => $tanque->capacidad_operativa,
                                'UnidadDeMedida' => 'UM03'
                            ],
                            'CapacidadUtilTanque' => [
                                'ValorNumerico' => $tanque->capacidad_util,
                                'UnidadDeMedida' => 'UM03'
                            ],
                            'CapacidadFondajeTanque' => [
                                'ValorNumerico' => $tanque->capacidad_fondaje,
                                'UnidadDeMedida' => 'UM03'
                            ],
                            'VolumenMinimoOperacion' => [
                                'ValorNumerico' => $tanque->volumen_minimo_operacion,
                                'UnidadDeMedida' => 'UM03'
                            ],
                            'EstadoTanque' => $tanque->estado,
                            'Medidores' => $medidoresArray,
                            // Obtener las existencias
                            
                            'Existencias' => [
                                'VolumenExistenciasAnterior' => round($inventarioInicialConv,3),
                                'VolumenAcumOpsRecepcion' => [
                                    'ValorNumerico' => $sumaRecepcionTanque,
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'HoraRecepcionAcumulado' => $horaCorte,
                                'VolumenAcumOpsEntrega' => [
                                    'ValorNumerico' => round($sumaEntregado,3),
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'HoraEntregaAcumulado' => $horaCorte,
                                'VolumenExistencias' => round(($inventarioInicialConv + $sumaRecepcionTanque - $sumaEntregado),3),
                                'FechaYHoraEstaMedicion' => $fechaHoraCorte,
                                'FechaYHoraMedicionAnterior' => $fechaHoraCorteAnt
                            ],
                            'Recepciones' => [
                                'TotalRecepciones' => $sumaDocumentosRecepcion,
                                'SumaVolumenRecepcion' => [
                                    'ValorNumerico' => $sumaRecepcionTanque,
                                    'UnidadDeMedida' => 'UM03'
                                ],
                                'TotalDocumentos' => $sumaDocumentosRecepcion,
                                'Recepcion' => $recepcionesOfTanque
                            ],
                            'Entregas' => [
                                'TotalEntregas' => $sumaDocumentosEntregados,
                                'SumaVolumenEntregado' => [
                                    'ValorNumerico' => round($sumaEntregado,3),
                                    'UnidadDeMedida' => 'UM03',
                                ],
                                'TotalDocumentos' => $sumaDocumentosEntregados,
                                'SumaVentas' => 0,
                            ]
                        ];
                    }
                } */

            }




        } catch (\Throwable $th) {
            echo $th->getMessage();
            $this->error('Error al generar json' . $th->getMessage());
        }
        



        

        /* Crear registros de salidas */

        /* Crear data json */

        /* Obtener Datos de (bitácora) de eventos */

        /* Guardar (archivo) o retornar la data */

        /* Crear excel archivo */
    }

    private function convertLitros($tipo, $valor, $densidad)
    {   
        return $tipo === 'tons' ? $valor : ($valor * 1000) / $densidad;
    }

}
