<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;
use Sabre\Xml\Service;
use Illuminate\Support\Facades\Log;
use App\Models\Cliente;
use App\Models\Importador;
use App\Models\Bodega;
use App\Models\Aduana;
use App\Models\Patente;

use App\Models\User;

class FacturaXMLController extends Controller
{
    // Mostrar el formulario de carga
    public function showUploadForm()
    {
        return view('facturas.upload');
    }

    // Procesar el archivo XML
    public function processXML(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:2048'
        ]);

        try {
            $file = $request->file('xml_file');
            $xmlService = new Service();
            $xml = $xmlService->parse(file_get_contents($file->getRealPath()));

            $operacionData = $this->extractOperacionData($xml);

            // Buscar cliente por RFC exacto
            $clienteMatch = Cliente::where('rfc', $operacionData['emisor_rfc'])->first();

            // Buscar importador por nombre similar (ya que el RFC es genérico XEXX010101000)
            $importadorMatch = Importador::where('nombre', 'like', '%' . $operacionData['receptor_nombre'] . '%')->first();

            // Obtener datos para los selectores
            $clientes = Cliente::all();
            $importadores = Importador::all();
            $bodegas = Bodega::all();
            $aduanas = Aduana::all();
            $patentes = Patente::all();
            $expedientes = Expediente::all();
            $documentadores = User::all();
            // Agrega más modelos según necesites (bodegas, aduanas, etc.)

            return view('facturas.form', compact(
                'operacionData',
                'clientes',
                'importadores',
                'bodegas',
                'aduanas',
                'patentes',
                'expedientes',
                'documentadores',
                'clienteMatch',
                'importadorMatch',
            ));

        } catch (\Exception $e) {
            Log::error("Error procesando XML: " . $e->getMessage());
            return back()->with('error', 'Error al procesar el archivo XML: ' . $e->getMessage());
        }
    }

    // Función para extraer datos del XML (personaliza según tu estructura XML)
    public function extractOperacionData($xml)
    {
        $data = [
            'fecha_registro' => null,
            'num_factura' => null,
            'nombre_producto' => null,
            'emisor_nombre' => null,
            'emisor_rfc' => null,
            'receptor_nombre' => null,
            'receptor_rfc' => null,
            'domicilio_emisor' => null,
            'domicilio_receptor' => null,
            'conceptos' => [],
            'moneda' => null,
            'tipo_cambio' => null,
            'subtotal' => null,
            'total' => null,
            'uuid' => null,
            'comercio_exterior' => []
        ];

        //Extraccion
        //Log::debug('Estructura completa del XML recibido:', ['xml' => $xml]);
        foreach ($xml as $element) {
            // Datos básicos del comprobante
            if ($element['name'] === '{http://www.sat.gob.mx/cfd/4}Comprobante') {
                $data['fecha_registro'] = substr($element['attributes']['Fecha'], 0, 10); // Solo la fecha sin hora
                //$data['num_factura'] = $element['attributes']['Folio'] ?? 'S/N';
                //$data['num_factura'] = $this->extractFolio($element['attributes']);
                $data['moneda'] = $element['attributes']['Moneda'] ?? 'MXN';
                $data['tipo_cambio'] = $element['attributes']['TipoCambio'] ?? 1;
                $data['subtotal'] = $element['attributes']['SubTotal'] ?? 0;
                $data['total'] = $element['attributes']['Total'] ?? 0;
                //dd($data);
            }
            if ($element['name'] === 'cfdi:Comprobante') {
                $data['fecha_registro'] = substr($element['attributes']['Fecha'], 0, 10); // Solo la fecha sin hora
                $data['num_factura'] = $element['attributes']['Folio'] ?? 'S/N';
                //$data['num_factura'] = $this->extractFolio($element['attributes']);
                $data['moneda'] = $element['attributes']['Moneda'] ?? 'MXN';
                $data['tipo_cambio'] = $element['attributes']['TipoCambio'] ?? 1;
                $data['subtotal'] = $element['attributes']['SubTotal'] ?? 0;
                $data['total'] = $element['attributes']['Total'] ?? 0;
                dd($data);
            }
            

            // Datos del emisor (cliente)
            if ($element['name'] === '{http://www.sat.gob.mx/cfd/4}Emisor') {
                $data['emisor_nombre'] = $element['attributes']['Nombre'] ?? '';
                $data['emisor_rfc'] = $element['attributes']['Rfc'] ?? '';
                //$data['num_factura'] = $element['attributes']['Folio'] ?? 'S/N';
                //$data['num_factura'] = $this->extractFolio($element['attributes']);

            }


            // Datos del receptor (importador)
            if ($element['name'] === '{http://www.sat.gob.mx/cfd/4}Receptor') {
                $data['receptor_nombre'] = $element['attributes']['Nombre'] ?? '';
                $data['receptor_rfc'] = $element['attributes']['Rfc'] ?? '';
                //$data['num_factura'] = $element['attributes']['Folio'] ?? 'S/N';
                //$data['num_factura'] = $this->extractFolio($element['attributes']);

            }

            // Conceptos (productos)
            if ($element['name'] === '{http://www.sat.gob.mx/cfd/4}Conceptos') {
                foreach ($element['value'] as $concepto) {
                    if ($concepto['name'] === '{http://www.sat.gob.mx/cfd/4}Concepto') {
                        $data['conceptos'][] = [
                            'descripcion' => $concepto['attributes']['Descripcion'] ?? '',
                            'cantidad' => $concepto['attributes']['Cantidad'] ?? 0,
                            'valor_unitario' => $concepto['attributes']['ValorUnitario'] ?? 0,
                            'importe' => $concepto['attributes']['Importe'] ?? 0
                        ];

                        // Tomamos el primer producto como nombre principal
                        if (empty($data['nombre_producto'])) {
                            $data['nombre_producto'] = $concepto['attributes']['Descripcion'] ?? '';
                        }
                    }
                }
            }

            // Complemento Comercio Exterior
            if ($element['name'] === '{http://www.sat.gob.mx/cfd/4}Complemento') {
                foreach ($element['value'] as $complemento) {
                    // Datos de Comercio Exterior
                    if ($complemento['name'] === '{http://www.sat.gob.mx/ComercioExterior20}ComercioExterior') {
                        $data['comercio_exterior'] = [
                            'clave_pedimento' => $complemento['attributes']['ClaveDePedimento'] ?? '',
                            'incoterm' => $complemento['attributes']['Incoterm'] ?? '',
                            'total_usd' => $complemento['attributes']['TotalUSD'] ?? 0
                        ];

                        // Domicilio del emisor
                        foreach ($complemento['value'] as $comercioElement) {
                            if ($comercioElement['name'] === '{http://www.sat.gob.mx/ComercioExterior20}Emisor') {
                                foreach ($comercioElement['value'] as $domicilio) {
                                    if ($domicilio['name'] === '{http://www.sat.gob.mx/ComercioExterior20}Domicilio') {
                                        $data['domicilio_emisor'] = [
                                            'calle' => $domicilio['attributes']['Calle'] ?? '',
                                            'numero_exterior' => $domicilio['attributes']['NumeroExterior'] ?? '',
                                            'colonia' => $domicilio['attributes']['Colonia'] ?? '',
                                            'municipio' => $domicilio['attributes']['Municipio'] ?? '',
                                            'estado' => $domicilio['attributes']['Estado'] ?? '',
                                            'pais' => $domicilio['attributes']['Pais'] ?? '',
                                            'codigo_postal' => $domicilio['attributes']['CodigoPostal'] ?? ''
                                        ];
                                    }
                                }
                            }

                            // Domicilio del receptor
                            if ($comercioElement['name'] === '{http://www.sat.gob.mx/ComercioExterior20}Receptor') {
                                foreach ($comercioElement['value'] as $domicilio) {
                                    if ($domicilio['name'] === '{http://www.sat.gob.mx/ComercioExterior20}Domicilio') {
                                        $data['domicilio_receptor'] = [
                                            'calle' => $domicilio['attributes']['Calle'] ?? '',
                                            'numero_exterior' => $domicilio['attributes']['NumeroExterior'] ?? '',
                                            'colonia' => $domicilio['attributes']['Colonia'] ?? '',
                                            'localidad' => $domicilio['attributes']['Localidad'] ?? '',
                                            'estado' => $domicilio['attributes']['Estado'] ?? '',
                                            'pais' => $domicilio['attributes']['Pais'] ?? '',
                                            'codigo_postal' => $domicilio['attributes']['CodigoPostal'] ?? ''
                                        ];
                                    }
                                }
                            }
                        }
                    }

                    // Timbre Fiscal Digital (UUID)
                    if ($complemento['name'] === '{http://www.sat.gob.mx/TimbreFiscalDigital}TimbreFiscalDigital') {
                        $data['uuid'] = $complemento['attributes']['UUID'] ?? '';
                    }
                }
            }
            //dd($data);
            //Log::debug('Atributos del Comprobante:', ['attributes' => $element['attributes']]);
        }
        //dd($element);
        return $data;

    }

    // Guardar los datos del formulario
    public function store(Request $request)
    {
        // Validación y lógica para guardar los datos
        $validated = $request->validate([
            'emisor' => 'required|string|max:255',
            'receptor' => 'required|string|max:255',
            'fecha_registro' => 'required|date',
            'total' => 'required|numeric',
            // Agrega más validaciones según necesites
        ]);

        // Aquí iría la lógica para guardar en la base de datos
        // ...

        return redirect()->route('facturas.upload')
            ->with('success', 'Factura guardada correctamente');
    }
    protected function extractFolio($attributes)
    {
        //dd('Extract Folio');
        // Primero intentamos obtener el folio directamente
        $folio = $attributes['Folio'] ?? null;

        // Si no hay folio pero hay serie, usamos la serie
        if (empty($folio) && !empty($attributes['Serie'])) {
            return $attributes['Serie'];
        }

        // Si hay ambos, combinamos serie y folio
        if (!empty($attributes['Serie']) && !empty($folio)) {
            return $attributes['Serie'] . '-' . $folio;
        }

        // Si no hay nada, devolvemos un valor por defecto
                dd($folio);

        return $folio ?? 'SIN-FOLIO';
    }
}