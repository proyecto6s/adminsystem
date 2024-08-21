<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BitacoraController;
use App\Models\Cargo;
use App\Models\Permisos;
use App\Models\empleados;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Rules\Validaciones;
use Barryvdh\DomPDF\Facade\Pdf;

class CargosControlador extends Controller
{
    protected $bitacora;

    public function __construct(BitacoraController $bitacora)
    {
        $this->bitacora = $bitacora;
    }

    public function index()
    {
        $user = Auth::user();
        $roleId = $user->Id_Rol;

        // Verificar si el rol del usuario tiene el permiso de consulta en el objeto SOLICITUD
        $permisoConsultar = Permisos::where('Id_Rol', $roleId)
            ->where('Id_Objeto', function ($query) {
                $query->select('Id_Objetos')
                    ->from('tbl_objeto')
                    ->where('Objeto', 'CARGO')
                    ->limit(1);
            })
            ->where('Permiso_Consultar', 'PERMITIDO')
            ->exists();

        if (!$permisoConsultar) {
            $this->bitacora->registrarEnBitacora(17, 'Intento de entrar a la ventana de cargos sin permisos', 'Error');

            return redirect()->route('dashboard')->withErrors('No tiene permiso para consultar cargos');
        }

        $response = Http::get('http://127.0.0.1:3000/cargos');
        $cargos = $response->json();
        $this->bitacora->registrarEnBitacora(17, 'Ingreso a la ventana de cargos', 'Ingreso');

        return view('cargos.index', compact('cargos'));
    }
    public function pdf(){
        $cargos=Cargo::all();

        //fecha y hora generada
        $fechaHora = \Carbon\Carbon::now()->format('d-m-Y H:i:s');
        

        //cambio de img a formato pdf
        $path = public_path('images/CTraterra.jpeg');
        $logoBase64 = 'data:image/' . pathinfo($path, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($path));
    
        //paginacion
        $pdf = Pdf::loadView('cargos.pdf', compact('cargos', 'fechaHora', 'logoBase64'))
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
        ]);

        $this->bitacora->registrarEnBitacora(17, 'Generacion de reporte de cargos', 'Ingreso');

        return $pdf->stream();
    }
    public function crear()
    {
        $user = Auth::user();
        $roleId = $user->Id_Rol;

        // Verificar si el rol del usuario tiene el permiso de inserción en el objeto COMPRA
        $permisoInsercion = Permisos::where('Id_Rol', $roleId)
            ->where('Id_Objeto', function ($query) {
                $query->select('Id_Objetos')
                    ->from('tbl_objeto')
                    ->where('Objeto', 'CARGO')
                    ->limit(1);
            })
            ->where('Permiso_Insercion', 'PERMITIDO')
            ->exists();

        if (!$permisoInsercion) {
            $this->bitacora->registrarEnBitacora(17, 'Intento de anadir cargos sin permisos', 'Error');

            return redirect()->route('cargos.index')->withErrors('No tiene permiso para crear cargos');
        }

        return view('cargos.crear');
    }

    public function insertar(Request $request)
    {
        // Validar la entrada
        $validator = Validator::make($request->all(), [
            'NOM_CARGO' => [
                'required',
                'string',
                'max:25', // Longitud máxima de 25 caracteres
                'unique:tbl_cargos,NOM_CARGO', // Asegura que el nombre del cargo sea único en la tabla tbl_cargos
                'regex:/^[A-ZÑÁÉÍÓÚ\s]+$/', // Solo letras mayúsculas y espacios
                'regex:/^(?!.*\s{2,}).*$/', // Prohibir múltiples espacios consecutivos
            ],
            'FUNCION_PRINCIPAL' => [
                'required',
                'string',
                'max:50', // Longitud máxima de 50 caracteres
                'regex:/^[A-ZÑÁÉÍÓÚ\s]+$/', // Solo letras mayúsculas y espacios
                'regex:/^(?!.*\s{2,}).*$/', // Prohibir múltiples espacios consecutivos
            ],
            'SALARIOS' => [
                'required',
                'numeric',
                'min:0.01', // No permitir cero ni valores negativos
                'regex:/^\d+(\.\d{1,2})?$/' // Permitir solo números y hasta dos decimales
            ],
        ], [
            'NOM_CARGO.required' => 'El nombre del cargo es obligatorio.',
            'NOM_CARGO.string' => 'El nombre del cargo debe ser una cadena de texto.',
            'NOM_CARGO.max' => 'El nombre del cargo no puede exceder los 25 caracteres.',
            'NOM_CARGO.unique' => 'El nombre del cargo ya existe.',
            'NOM_CARGO.regex' => 'El nombre del cargo debe contener solo letras mayúsculas y espacios, sin números ni símbolos especiales, y sin espacios consecutivos.',
            'FUNCION_PRINCIPAL.required' => 'La función principal es obligatoria.',
            'FUNCION_PRINCIPAL.string' => 'La función principal debe ser una cadena de texto.',
            'FUNCION_PRINCIPAL.max' => 'La función principal no puede exceder los 50 caracteres.',
            'FUNCION_PRINCIPAL.regex' => 'La función principal debe contener solo letras mayúsculas y espacios, sin números ni símbolos especiales, y sin espacios consecutivos.',
            'SALARIOS.required' => 'El salario es obligatorio.',
            'SALARIOS.numeric' => 'El salario debe ser un número.',
            'SALARIOS.min' => 'El salario no puede ser cero ni negativo.',
            'SALARIOS.regex' => 'El salario debe ser un número positivo con hasta dos decimales.',
        ]);
    
        // Verifica si la validación falla
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $response = Http::post('http://127.0.0.1:3000/INS_CARGOS', [
            'NOM_CARGO' => $request->NOM_CARGO,
            'SALARIOS' => $request->SALARIOS,
            'FUNCION_PRINCIPAL' => $request->FUNCION_PRINCIPAL,
            'ESTADO' => 'ACTIVO'
        ]);

        $this->bitacora->registrarEnBitacora(17, 'Nuevo cargo anadido exitosamente', 'Insert');

        return redirect()->route('cargos.index');
    }

    public function destroy($COD_CARGO)
{
    $user = Auth::user();
    $roleId = $user->Id_Rol;

    // Verificar si el rol del usuario tiene el permiso de eliminación en el objeto CARGO
    $permisoEliminacion = Permisos::where('Id_Rol', $roleId)
        ->where('Id_Objeto', function ($query) {
            $query->select('Id_Objetos')
                ->from('tbl_objeto')
                ->where('Objeto', 'CARGO')
                ->limit(1);
        })
        ->where('Permiso_Eliminacion', 'PERMITIDO')
        ->exists();

    if (!$permisoEliminacion) {
        $this->bitacora->registrarEnBitacora(17, 'Intento de eliminar cargos sin permisos', 'Error');

        return redirect()->route('cargos.index')->withErrors('No tiene permiso para eliminar cargos');
    }

    // Verificar si hay empleados asignados a este cargo
    $empleadosAsignados = empleados::where('COD_CARGO', $COD_CARGO)->exists();

    if ($empleadosAsignados) {
        return redirect()->route('cargos.index')->withErrors('No se puede eliminar el cargo porque hay empleados asignados a él.');
    }

    // Encontrar el cargo por su ID
    $cargo = Cargo::findOrFail($COD_CARGO);

    // Cambiar el estado del cargo a INACTIVO
    $cargo->ESTADO = 'INACTIVO';
    $cargo->save();

    $this->bitacora->registrarEnBitacora(17, 'Cargo eliminado exitosamente', 'Delete');

    return redirect()->route('cargos.index')->with('success', 'Cargo eliminado correctamente');
}

    public function edit($COD_CARGO)
    {
        $user = Auth::user();
        $roleId = $user->Id_Rol;

        // Verificar si el rol del usuario tiene el permiso de actualización en el objeto COMPRA
        $permisoActualizacion = Permisos::where('Id_Rol', $roleId)
            ->where('Id_Objeto', function ($query) {
                $query->select('Id_Objetos')
                    ->from('tbl_objeto')
                    ->where('Objeto', 'CARGO')
                    ->limit(1);
            })
            ->where('Permiso_Actualizacion', 'PERMITIDO')
            ->exists();

        if (!$permisoActualizacion) {
            $this->bitacora->registrarEnBitacora(17, 'Intento de actualizar cargos sin permisos', 'Error');

            return redirect()->route('cargos.index')->withErrors('No tiene permiso para editar cargos');
        }

        $response = Http::get("http://localhost:3000/Cargos/{$COD_CARGO}");
        $cargos = $response->json();

        if (!isset($cargos['COD_CARGO'])) {
            dd('COD_CARGO no está definido en la respuesta de la API', $cargos);
        }

        return view('cargos.edit', compact('cargos'));
    }

    public function update(Request $request, $COD_CARGO)
    {
        // Validar la entrada
    $validator = Validator::make($request->all(), [
        'NOM_CARGO' => [
            (new Validaciones)
                ->requerirTodoMayusculas()
                ->prohibirNumerosSimbolos()
                ->prohibirMultiplesEspacios()
                ->requerirCampo()
                ->requerirLongitudMaxima(25)
                ->requerirSinEspacios()
                ->prohibirEspaciosInicioFin()
        ],
        'SALARIOS' => [
            (new Validaciones)
                ->requerirSoloNumeros()
                ->requerirSinEspacios()
                ->requerirCampo()
                ->requerirValorMinimo(1)
                ->prohibirCeroYNegativos()
                ->requerirLongitudMaxima(11)
                ->prohibirSimbolosSalvoDecimal()
        ],
        'FUNCION_PRINCIPAL' => [
            (new Validaciones)
                ->requerirTodoMayusculas()
                ->prohibirNumerosSimbolos()
                ->requerirCampo()
                ->requerirLongitudMaxima(50)
                ->prohibirMultiplesEspacios()
                ->prohibirEspaciosInicioFin()
        ],
    ], [
        'NOM_CARGO' => 'Nombre del Cargo',
        'SALARIOS' => 'Salarios',
        'FUNCION_PRINCIPAL' => 'Función Principal',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

        $response = Http::put("http://127.0.0.1:3000/cargos/{$COD_CARGO}", [
            'NOM_CARGO' => $request->NOM_CARGO,
            'SALARIOS' => $request->SALARIOS,
            'FUNCION_PRINCIPAL' => $request->FUNCION_PRINCIPAL,
        ]);

        $this->bitacora->registrarEnBitacora(17, 'Cargo actualizado correctamente', 'Update');

        return redirect()->route('cargos.index');
    }
}