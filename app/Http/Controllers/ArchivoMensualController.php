<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArchivoMensualResource;
use App\Models\ArchivoMensual;
use Illuminate\Http\Request;

class ArchivoMensualController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $archivos = ArchivoMensual::orderBy('estado', 'asc')->orderBy('id', 'asc')->paginate(15);
        $archivos->load('usuario');
        $archivos = ArchivoMensualResource::collection($archivos)->additional([
            'status' => 'success',
            "message" => 'Información consultada correctamente.',
        ]);
        
        return $archivos;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ArchivoMensual  $archivoMensual
     * @return \Illuminate\Http\Response
     */
    public function show(ArchivoMensual $archivoMensual)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ArchivoMensual  $archivoMensual
     * @return \Illuminate\Http\Response
     */
    public function edit(ArchivoMensual $archivoMensual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ArchivoMensual  $archivoMensual
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ArchivoMensual $archivoMensual)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ArchivoMensual  $archivoMensual
     * @return \Illuminate\Http\Response
     */
    public function destroy(ArchivoMensual $archivoMensual)
    {
        //
    }
}
