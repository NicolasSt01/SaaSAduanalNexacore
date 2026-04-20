<?php

namespace App\Services;

use App\Models\Operacion;
use App\Models\Cliente;
use App\Models\Importador;
use App\Models\Bodega;
use App\Models\Aduana;
use App\Models\Patente;
use App\Models\Expediente;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OperacionImportService
{
    protected $errores = [];
    protected $exitosos = 0;
    protected $omitidos = 0;

    public function procesarFila($fila, $index)
    {
        $referencia = $fila['referer'] ?? null;

        // Validar que tenga referencia
        /*if (empty($referencia)) {
            $this->registrarError($index, "Sin número de referencia");
            return false;
        }

        // Validar que la referencia no esté duplicada
        if (Operacion::where('referencia', $referencia)->exists()) {
            $this->registrarError($referencia, "Referencia duplicada");
            return false;
        }*/
        
        // Validar que tenga referencia
if (empty($referencia)) {
    $this->registrarError($index, "Sin número de referencia");
    return false;
}

// Limpiar la referencia (quitar espacios y normalizar)
$referencia = strtoupper(trim($referencia));

// Validar que la referencia no esté duplicada (case-insensitive)
if (Operacion::whereRaw('UPPER(TRIM(referencia)) = ?', [$referencia])->exists()) {
    $this->registrarError($referencia, "Referencia duplicada en el sistema");
    return false;
}

        // Validar factura
        $factura = $fila['factura'] ?? null;
        if (empty($factura)) {
            $this->registrarError($referencia, "Falta número de factura");
            return false;
        }

        // Buscar Cliente
        $cliente = Cliente::where('nombre_empresa', 'LIKE', '%' . trim($fila['cliente']) . '%')->first();
        if (!$cliente) {
            $this->registrarError($referencia, "Cliente '{$fila['cliente']}' no encontrado");
            return false;
        }

        // Buscar Importador
        $importador = Importador::where('nombre', 'LIKE', '%' . trim($fila['importador']) . '%')->first();
        if (!$importador) {
            $this->registrarError($referencia, "Importador '{$fila['importador']}' no encontrado");
            return false;
        }

        // Buscar Bodega
        /*$bodega = Bodega::where('nombre_bodega', 'LIKE', '%' . trim($fila['bodega']) . '%')->first();
        if (!$bodega) {
            $this->registrarError($referencia, "Bodega '{$fila['bodega']}' no encontrada");
            return false;
        }*/

        // Buscar Aduana
        $aduana = Aduana::where('nombre_aduana', 'LIKE', '%' . trim($fila['aduana']) . '%')
            ->orWhere('clave_aduana', 'LIKE', '%' . trim($fila['aduana']) . '%')
            ->first();
        if (!$aduana) {
            $this->registrarError($referencia, "Aduana '{$fila['aduana']}' no encontrada");
            return false;
        }

        // Buscar Patente
        
        $patente_id=null;
        $patente_valor=trim($fila['patente'] ?? '');

        if (!empty($patente_valor)){
            $patente= Patente::where('numero_patente',$patente_valor)->first();
            if($patente){
                $patente_id=$patente->id;
            }else{
                // ⚠️ ADVERTENCIA: No existe pero se registra como null
                $this->registrarError($referencia, "Patente '{$patente_valor}' no encontrado - Se registro como Null");
            }
        }
        /*$patente = Patente::where('numero_patente', trim($fila['patente']))->first();
        if (!$patente) {
            $this->registrarError($referencia, "Patente '{$fila['patente']}' no encontrada");
            return false;
        }*/
        

        // Buscar Expediente (Pedimento)
        /*$expediente = Expediente::where('numero_pedimento', trim($fila['pedimen']))->first();
        if (!$expediente) {
            $this->registrarError($referencia, "Pedimento '{$fila['pedimen']}' no encontrado");
            return false;
        }*/
        // Buscar Expediente/Pedimento (opcional)
        /*$pedimento_id = null;
        if (!empty($fila['pedimen']) && trim($fila['pedimen']) !== '') {
            $expediente = Expediente::where('numero_pedimento', trim($fila['pedimen']))->first();
            if (!$expediente) {
                $this->registrarError($referencia, "Pedimento '{$fila['pedimen']}' no encontrado");
                return false;
            }
            $pedimento_id = $expediente->id;
        }*/
        // ✅ Buscar Expediente/Pedimento (OPCIONAL - registra como null si no existe)
        $pedimento_id = null;
        $pedimento_valor = trim($fila['pedimen'] ?? '');

        if (!empty($pedimento_valor)) {
            $expediente = Expediente::where('numero_pedimento', $pedimento_valor)->first();

            if ($expediente) {
                $pedimento_id = $expediente->id;
            } else {
                // ⚠️ ADVERTENCIA: No existe pero se registra como null
                $this->registrarError($referencia, "Pedimento '{$pedimento_valor}' no encontrado - se registró como NULL");
            }
        }

        // Buscar usuario asignado (opcional)
        /*$usuario_cierre_id = null;
        if (!empty($fila['asignar_a'])) {
            $usuario = User::where('name', 'LIKE', '%' . trim($fila['asignar_a']) . '%')->first();
            $usuario_cierre_id = $usuario ? $usuario->id : null;
        }*/
        
        // Buscar documentador (quien registra)
        $usuario_registro_id = auth()->id(); // Por defecto el usuario actual
        if (!empty($fila['asignar_a'])) {
            $documentador = User::where('name', 'LIKE', '%' . trim($fila['asignar_a']) . '%')->first();
            if ($documentador) {
                $usuario_registro_id = $documentador->id;
            }
        }
        
        $bodega_id = null;
        if (!empty($fila['bodega']) && trim($fila['bodega']) !== '') {
            $bodega = Bodega::where('nombre_bodega', 'LIKE', '%' . trim($fila['bodega']) . '%')->first();
            if (!$bodega) {
                $this->registrarError($referencia, "Bodega '{$fila['bodega']}' no encontrada");
                return false;
            }
            $bodega_id = $bodega->id;
        }


        // Buscar usuario asignado (opcional) - por si en el futuro quieres usar otra columna
        $usuario_cierre_id = null;

        // Determinar estado según NUM DODA
        //$estado = !empty($fila['num_doda']) ? 'terminado' : 'pendiente';
        // Validar y limpiar número de DODA (debe tener exactamente 9 dígitos)
        $num_doda = null;
        if (!empty($fila['num_doda'])) {
            // Limpiar el valor (quitar espacios, guiones, etc)
            $doda_limpio = preg_replace('/[^0-9]/', '', $fila['num_doda']);

            // Validar que tenga exactamente 9 dígitos
            if (strlen($doda_limpio) === 9) {
                $num_doda = $doda_limpio;
            }
        }

        // Determinar estado según NUM DODA válido
        $estado = !empty($num_doda) ? 'terminado' : 'pendiente';


        // Parsear fecha
        $fecha = $this->parsearFecha($fila['fecha']);

        // Determinar sobrepeso
        /*$sobrepeso = false;
        if (isset($fila['sobrepeso']) && strtoupper(trim($fila['sobrepeso'])) !== 'N/A') {
            $sobrepeso = true;
        }*/
        // Determinar sobrepeso
        // 0 = sin sobrepeso (si está vacío o es N/A)
        // 1 = con sobrepeso (cualquier otro valor)
        $sobrepeso = 0;
        if (!empty($fila['sobrepeso'])) {
            $valor_limpio = strtoupper(trim($fila['sobrepeso']));

            // Validar formato: Letra seguida de 6-7 dígitos (ejemplo: C760725)
            if ($valor_limpio !== 'N/A' && preg_match('/^[A-Z]\d{6,7}$/', $valor_limpio)) {
                $sobrepeso = 1;
            }
        }


        // Crear exportación
        try {
            Operacion::create([
                'referencia' => $referencia,
                'fecha' => $fecha,
                'cliente_id' => $cliente->id,
                'importador_id' => $importador->id,
                'nombre_producto' => $fila['producto'] ?? null,
                'bodega_id' => $bodega_id,
                'num_factura' => $factura,
                'aduana_id' => $aduana->id,
                'patente_id' => $patente_id,
                'pedimento_id' => $pedimento_id,
                'num_thermo' => $fila['thermo'] ?? null,
                'codigo_alpha' => $fila['alfa'] ?? null,
                'num_doda' => $num_doda,
                'modulacion' => 0, // Por defecto
                'usuario_registro_id' => $usuario_registro_id,
                'usuario_cierre_id' => ($estado === 'terminado') ? auth()->id() : null,
                'prioridad' => 'regular',
                'estado' => $estado,
                'sobrepeso' => $sobrepeso,
            ]);

            $this->exitosos++;
            return true;

        } catch (\Exception $e) {
            $this->registrarError($referencia, "Error al crear registro: " . $e->getMessage());
            return false;
        }
    }

    protected function parsearFecha($fecha)
    {
        if (empty($fecha)) {
            return now();
        }

        try {
            // Intentar varios formatos de fecha
            if (is_numeric($fecha)) {
                // Fecha de Excel (número serial)
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha));
            }

            return Carbon::parse($fecha);
        } catch (\Exception $e) {
            return now();
        }
    }

    protected function registrarError($referencia, $mensaje)
    {
        $this->errores[] = "Referencia #{$referencia}: {$mensaje}";
        $this->omitidos++;
    }

    public function getResultados()
    {
        return [
            'exitosos' => $this->exitosos,
            'omitidos' => $this->omitidos,
            'errores' => $this->errores,
        ];
    }

    public function generarLog()
    {
        $contenido = "=== LOG DE IMPORTACIÓN DE EXPORTACIONES ===\n";
        $contenido .= "Fecha: " . now()->format('Y-m-d H:i:s') . "\n";
        $contenido .= "Usuario: " . auth()->user()->name . "\n\n";
        $contenido .= "RESUMEN:\n";
        $contenido .= "- Registros exitosos: {$this->exitosos}\n";
        $contenido .= "- Registros omitidos: {$this->omitidos}\n\n";

        if (count($this->errores) > 0) {
            $contenido .= "DETALLE DE ERRORES:\n";
            foreach ($this->errores as $error) {
                $contenido .= "- {$error}\n";
            }
        }

        return $contenido;
    }
}