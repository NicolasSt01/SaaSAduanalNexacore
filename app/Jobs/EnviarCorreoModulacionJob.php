<?php

namespace App\Jobs;

use Exception;
use App\Models\Cliente;
use App\Mail\EstatusModulacionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;

class EnviarCorreoModulacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $clienteId;
    public $datosTramite;
    public $status_txt;
    
    public $estatusRojos = [
        'RECONOCIMIENTO ADUANERO',
        'RECONOCIMIENTO ADUANERO CONCLUIDO',
    ];

    private $clientesSoloRojos = [
        'MAINLAND FARMS S. A. DE C. V.',
    ];
    
    // Mapeo de clientes y sus correos
    private $clientesPermitidos = [
        'FRUTCOM DE MEXICO' => [
            'comercioexterior@frutcom.net',
            'aux.admin@frutcom.net',
        ],
        'NR AVOCADOS S.A DE C.V' => [
            'ygaona@baikafruit.com',
            'rhauyon@baikafruit.com',
            'smoreno@baikafruit.com',
            'msilva@baikafruit.com',
        ],
        'FRUITDOME MEXICO S.A. DE C.V.' => [
            'comercio.exterior@fruitdomemx.com.mx',
            'francisco.gerencia@fruitdomemx.com.mx',
            'alejandro.logistica@fruitdomemx.com.mx',
        ],
        'MAINLAND FARMS S. A. DE C. V.' => [
            'trafico@mainlandfarms.com',
            'enava@mainlandfarms.com',
            'jesus@mainlandfarms.com',
            'elerch@mainlandfarms.com',
        ],
        'GIANT BERRY FARMS DE MEXICO S. DE R.L. DE C.V.' => [
            'aalonso@calgiant.mx',
            'jbernal@calgiant.mx',
            'jguizar@calgiant.mx',
            'jramirez@calgiant.com',
            
        ],
        'AMORE FRUITS PRODUCE S. DE R.L' => [
            'logistica@amorefruits.mx',
            
        ],
        'FRUTIVAL S.A DE C.V.' => [
            'logistica3@frutival.mx',
            'logistica2@frutival.mx',
            'logistica1@frutival.mx',
            'gerente_general@frutival.mx',
            'contraloria@frutival.mx',
            
        ],
        'AVOHOME S. de R.L. de C. V' => [
            'logistica@avohome.com.mx',
            'hugo@avohome.com.mx',
            'hgodinez@avohome.com.mx',
        ],
        'NATURE GROWN FRUITS AND VEGETABLES S.A. DE C.V.' => [
            'ng.logistica@hotmail.com',
            'nature.grown23@hotmail.com',
            'ngcosteo@hotmail.com',
            'ng-finanzas@hotmail.com',
            'ng.conta@outlook.com',
        ],
        'AVORAYO S.A. DE C.V.' => [
            'embarques@avorayo.com',
            'max@avorayo.com',
            'yadirag@avorayo.com',
            'eduardo@avorayo.com',
        ],
        'AGUACATES MI RANCHITO S.A. DE C.V.' => [
            'logisticamiranchitoo@gmail.com',
            'operacionmiranchito@gmail.com',
            'admiranchito@gmail.com',
        ],
        'AGRANA FRUIT MEXICO S.A DE C.V' => [
            'juan.ortiz@agrana.com',
            'janneth.cano@agrana.com',
            
        ],
        'MORIBITO DE MEXICO S. DE R.L' => [
            'stephania.madrigal@gmail.com',
            'miguelmp1992@gmail.com',
            'carmeno@moribitodemexico.com.mx',
            
        ],
        'OPTIMAL BRIGHTNESS SOLUTIONS S. DE R.L. DE C.V.' => [
            'rlanda@optimalgrowing.com',
            'anavarro@optimalbrightness.com',
            'frodriguez@optimalgrowing.com',
            'pedro@optimalgrowing.com',
            'rperalta@optimalgrowing.com',
            'jorge.guerra@optimalberry.com',
        ],
        'ULTRA FARMS DE MEXICO, SPR DE RL' => [
            'pablos@ultraclh.com',
            'arojas@calgiant.com',
            'fssupport@calgiant.com',
            
        ],
        
    ];

    public function __construct($clienteId, $datosTramite, $status_txt)
    {
        $this->clienteId = $clienteId;
        $this->datosTramite = $datosTramite;
        $this->status_txt = $status_txt;
    }

   
    public function handle()
    {
        try {
            $cliente = Cliente::find($this->clienteId);

            if (!$cliente) {
                \Log::error("JOB ERROR: clienteId {$this->clienteId} no encontrado");
                return;
            }

            // Verificar si el cliente está en la lista permitida
            if (!isset($this->clientesPermitidos[$cliente->nombre_empresa])) {
                // Enviar SOLO a sistemas si el cliente no está definido
                Mail::to('sistemas@crosspointmx.com')
                    ->bcc([

                        'trafico3@crosspointmx.com',
                        'gerencia@crosspointmx.com',
                        'operaciones@crosspointmx.com',
                        'operacionesreynosa@crosspointmx.com',
                        'trafico2@crosspointmx.com',
                        'practicante@crosspointmx.com',
                        'ventas2@crosspointmx.com',
                        'alejandro@crosspointmx.com',
                    ])
                    ->send(new EstatusModulacionMail(
                        $cliente,
                        $this->datosTramite,
                        $this->status_txt
                    ));

                \Log::info("Correo enviado SOLO a sistemas@crosspoint.com.mx (Cliente NO en lista) - Cliente: {$cliente->nombre_empresa}, ID: {$cliente->id}");
                return;
            }
            //Obtener el estatus actual del correo.
            $estatusActual = strtoupper($this->status_txt);
            // Clientes que solo quieren correos rojos
            if (
                in_array($cliente->nombre_empresa, $this->clientesSoloRojos) &&
                !in_array($estatusActual, $this->estatusRojos)
            ) {
                // Enviar SOLO a sistemas si el cliente no quiere el correo en verde
                Mail::to('sistemas@crosspointmx.com')
                    ->bcc([

                        'trafico3@crosspointmx.com',
                        'gerencia@crosspointmx.com',
                        'operaciones@crosspointmx.com',
                        'operacionesreynosa@crosspointmx.com',
                        'trafico2@crosspointmx.com',
                        'practicante@crosspointmx.com',
                        'ventas2@crosspointmx.com',
                        'alejandro@crosspointmx.com',
                    ])
                    ->send(new EstatusModulacionMail(
                        $cliente,
                        $this->datosTramite,
                        $this->status_txt
                    ));
                \Log::info("Correo NO enviado (cliente solo rojos) - Cliente: {$cliente->nombre_empresa}, Estatus: {$estatusActual}");
                return;
            }

            // Obtener los correos del cliente
            $correosCliente = $this->clientesPermitidos[$cliente->nombre_empresa];

            // Enviar el correo
            Mail::to($correosCliente[0]) // Primer correo como destinatario principal
                ->cc(array_slice($correosCliente, 1)) // Resto en copia
                ->bcc([
                    'sistemas@crosspointmx.com',
                    'gerencia@crosspointmx.com',
                    'alejandro@crosspointmx.com',
                    'operaciones@crosspointmx.com',
                    'trafico3@crosspointmx.com',
                    'operacionesreynosa@crosspointmx.com',
                    'trafico2@crosspointmx.com',
                    'practicante@crosspointmx.com',
                    'ventas2@crosspointmx.com',
                ])
                ->send(new EstatusModulacionMail(
                    $cliente,
                    $this->datosTramite,
                    $this->status_txt
                ));

            \Log::info("Correo enviado correctamente (JOB) - Cliente: {$cliente->nombre}, ClienteID: {$cliente->id}, Correos: " . implode(', ', $correosCliente));

        } catch (Exception $e) {
            \Log::error("JOB Ejecutado con Error - ClienteID: {$this->clienteId}, Error: {$e->getMessage()}");
        }
    }
    
    
}
