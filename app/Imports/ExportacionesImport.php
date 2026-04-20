<?php

namespace App\Imports;

use App\Models\Operacion;
use App\Services\OperacionImportService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class OperacionesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected $service;

    public function __construct(OperacionImportService $service)
    {
        $this->service = $service;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Mapear las columnas del Excel a un array más limpio
        $fila = [
            'referer' => $row['referencia'] ?? null,
            'fecha' => $row['fecha'] ?? null,
            'cliente' => $row['cliente'] ?? null,
            'importador' => $row['importador'] ?? null,
            'producto' => $row['producto'] ?? null,
            'bodega' => $row['bodega'] ?? null,
            'factura' => $row['factura'] ?? null,
            'aduana' => $row['aduana'] ?? null,
            'patente' => $row['patente'] ?? null,
            'pedimen' => $row['pedimento'] ?? null,
            'thermo' => $row['thermo'] ?? null,
            'alfa' => $row['alpha'] ?? null,
            'num_doda' => $row['numero_de_doda'] ?? null,
            'asignar_a' => $row['documentador'] ?? null,
            'sobrepeso' => $row['permiso_de_sobrepeso'] ?? null,
        ];

        // Procesar la fila a través del service
        $this->service->procesarFila($fila, $row['refererencia'] ?? 'Sin referencia');

        // Retornar null porque el service ya crea el modelo
        return null;
    }

    /**
     * Especificar que la primera fila son los encabezados
     */
    public function headingRow(): int
    {
        return 3; // Los encabezados están en la fila 3 según tu imagen
    }
}