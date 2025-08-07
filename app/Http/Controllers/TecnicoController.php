<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TecnicoController extends Controller
{
    /**
     * Mostrar todos los dispositivos con su técnico y cerrada relacionados.
     */
    public function index()
    {
        $catalogos = CatalogoDispositivo::with(['tecnico', 'cerrada'])->get();

        return response()->json([
            'success' => true,
            'data'    => $catalogos
        ], 200);
    }

    /**
     * Agregar un nuevo dispositivo.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'Identificador'         => 'required|exists:cerradas,id',
            'tecnico_id'            => 'required|exists:users,id',
            'archivo_configuracion' => 'required|string|max:512',
            'detalles'              => 'required|array|min:1',
            'detalles.*.uid'        => 'required|string|max:64',
            'detalles.*.nombre_dispositivo' => 'required|string|max:255',
        ], [
            'Identificador.required'         => 'El identificador (cerrada) es obligatorio.',
            'Identificador.exists'           => 'La cerrada seleccionada no existe.',
            'tecnico_id.required'            => 'El técnico es obligatorio.',
            'tecnico_id.exists'              => 'El técnico seleccionado no existe.',
            'archivo_configuracion.required' => 'El archivo de configuración es obligatorio.',
            'archivo_configuracion.max'      => 'El archivo de configuración no puede exceder de :max caracteres.',
            'detalles.required'              => 'Debe especificar al menos un detalle de dispositivo.',
            'detalles.array'                 => 'Los detalles deben enviarse como un arreglo.',
            'detalles.min'                   => 'Debe contener al menos un detalle.',
            'detalles.*.uid.required'        => 'El UID es obligatorio en cada detalle.',
            'detalles.*.uid.max'             => 'El UID no puede exceder de :max caracteres.',
            'detalles.*.nombre_dispositivo.required' => 'El nombre del dispositivo es obligatorio en cada detalle.',
            'detalles.*.nombre_dispositivo.max'      => 'El nombre del dispositivo no puede exceder de :max caracteres.',
        ]);

        $catalogo = CatalogoDispositivo::create([
            'cerrada_id'            => $validated['Identificador'],
            'tecnico_id'            => $validated['tecnico_id'],
            'archivo_configuracion' => $validated['archivo_configuracion'],
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $catalogo->detalles()->create([
                'uid'                => $detalle['uid'],
                'nombre_dispositivo' => $detalle['nombre_dispositivo'],
            ]);
        }

        $catalogo->load(['detalles', 'cerrada']);

        $groupSlug = Str::slug($catalogo->cerrada->group_name, '_');

        $files  = Storage::disk('s3')->files('firmware');
        $maxVer = 0;
        foreach ($files as $file) {
            if (preg_match("/^{$groupSlug}v(\d+)\.json$/", basename($file), $m)) {
                $maxVer = max($maxVer, (int)$m[1]);
            }
        }

        $version  = $maxVer + 1;
        $fileName = "{$groupSlug}v{$version}.json";

        $jsonContent = json_encode($catalogo->toArray(), JSON_PRETTY_PRINT);

        Storage::disk('s3')->put("firmware/{$fileName}", $jsonContent);

        $baseUrl = config('filesystems.disks.s3.url');
        $fileUrl = rtrim($baseUrl, '/') . '/firmware/' . $fileName;

        return response()->json([
            'success'      => true,
            'message'      => 'Catálogo y detalles creados, JSON subido correctamente.',
            'catalogo'     => $catalogo,
            'version'      => "v{$version}",
            'archivo_json' => $fileName,
            'url_archivo'  => $fileUrl,
        ], 201);
    }
    /**
     * Actualizar un dispositivo existente.
     */
    public function update(Request $request, $id)
    {
        $catalogo = CatalogoDispositivo::findOrFail($id);

        $validated = $request->validate([
            'cerrada_id'             => 'required|exists:cerradas,id',
            'tecnico_id'             => 'required|exists:users,id',
            'uid'                    => 'required|string|max:64',
            'archivo_configuracion'  => 'required|string|max:512',
            'nombre_dispositivo'     => 'required|string|max:255',
        ], [
            'cerrada_id.required'            => 'La cerrada es obligatoria.',
            'cerrada_id.exists'              => 'La cerrada seleccionada no existe.',
            'tecnico_id.required'            => 'El técnico es obligatorio.',
            'tecnico_id.exists'              => 'El técnico seleccionado no existe.',
            'uid.required'                   => 'El UID es obligatorio.',
            'uid.string'                     => 'El UID debe ser un texto.',
            'uid.max'                        => 'El UID no puede exceder de :max caracteres.',
            'archivo_configuracion.required' => 'El archivo de configuración es obligatorio.',
            'archivo_configuracion.string'   => 'El archivo de configuración debe ser un texto.',
            'archivo_configuracion.max'      => 'El archivo de configuración no puede exceder de :max caracteres.',
            'nombre_dispositivo.required'    => 'El nombre del dispositivo es obligatorio.',
            'nombre_dispositivo.string'      => 'El nombre del dispositivo debe ser un texto.',
            'nombre_dispositivo.max'         => 'El nombre del dispositivo no puede exceder de :max caracteres.',
        ]);

        $catalogo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo actualizado correctamente.',
            'data'    => $catalogo->load(['tecnico', 'cerrada'])
        ], 200);
    }

    /**
     * Eliminar (soft delete) un dispositivo.
     */
    public function destroy($id)
    {
        $catalogo = CatalogoDispositivo::findOrFail($id);
        $catalogo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo eliminado correctamente.'
        ], 200);
    }
}
