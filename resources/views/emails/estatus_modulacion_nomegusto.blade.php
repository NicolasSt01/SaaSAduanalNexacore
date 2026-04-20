<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Trámite - Crosspoint</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, sans-serif;">
    
    <!-- Contenedor principal con tabla -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f3f4f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Email container -->
                <table width="650" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; max-width: 650px;">
                    
                    <!-- HEADER -->
                    <tr>
                        <td style="background-color: #1a365d; padding: 40px 30px; text-align: center;">
                            <img src="https://salassys.com/wp-content/uploads/2025/11/white-2.png" alt="Logo Crosspoint" width="200" style="max-width: 200px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>
                    
                    @php
                        $estatusUpper = strtoupper($estatus);
                        
                        if ($estatusUpper === 'DESADUANAMIENTO LIBRE') {
                            $bgColor = '#10b981'; // Verde
                            $statusIcon = '✓';
                            $statusSubtitle = 'Su trámite ha sido completado exitosamente';

                        } elseif ($estatusUpper === 'RECONOCIMIENTO ADUANERO CONCLUIDO') {
                            // 🔹 Nuevo estatus separado
                            $bgColor = '#DC143C'; // Naranja (puedes cambiarlo)
                            $statusIcon = '✓';
                            $statusSubtitle = 'El reconocimiento aduanero ha concluido';
                        
                        } elseif (in_array($estatusUpper, [
                            'RECONOCIMIENTO ADUANERO',
                            'TRAMITE EN PROCESO DE REVISION'
                        ])) {
                            // 🔹 Estatus en proceso de inspección
                            $bgColor = '#DC143C'; // Rojo
                            $statusIcon = '🔍';
                            $statusSubtitle = 'Su trámite está en proceso de inspección';

                    } else {
                            // 🔹 Default
                            $bgColor = '#3b82f6'; // Azul por defecto
                            $statusIcon = 'ℹ️';
                            $statusSubtitle = 'Su trámite está en proceso';
                        }
                    @endphp
                    
                    <!-- STATUS CARD -->
                    <tr>
                        <td style="background-color: {{ $bgColor }}; padding: 50px 30px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <!-- Icon container -->
                                        <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto 25px;">
                                            <tr>
                                                <td align="center" style="width: 100px; height: 100px; background-color: rgba(255, 255, 255, 0.2); border-radius: 50%; font-size: 45px; font-weight: bold; color: #ffffff; vertical-align: middle;">
                                                    {{ $statusIcon }}
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <h1 style="margin: 0 0 10px 0; padding: 0; font-size: 32px; font-weight: 700; color: #ffffff; line-height: 1.2;">
                                            ¡{{ $estatus }}!
                                        </h1>
                                        
                                        <p style="margin: 0; padding: 0; font-size: 18px; color: #ffffff; opacity: 0.95;">
                                            {{ $statusSubtitle }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- CONTENT -->
                    <tr>
                        <td style="padding: 45px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 25px 0; font-size: 20px; color: #1a202c; font-weight: 600;">
                                            Estimado Cliente {{ $cliente['nombre'] }},
                                        </p>
                                        {{--<p style="margin: 0 0 25px 0; font-size: 20px; color: #1a202c; font-weight: 600;">
                                            Estimado Cliente {{ $cliente['nombre'] }}, 
                                        </p>--}
                                        
                                        <p style="margin: 0 0 35px 0; font-size: 16px; color: #4a5568; line-height: 1.7;">
                                            Le informamos que el siguiente trámite ha sido procesado. 
                                            A continuación, encontrará los detalles completos de su operación:
                                        </p>
                                        
                                        <!-- DETAILS CARD -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f7fafc; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 35px;">
                                            <tr>
                                                <td style="padding: 30px;">
                                                    <h2 style="margin: 0 0 25px 0; font-size: 20px; color: #2d3748; font-weight: 700;">
                                                        📋 Detalles del trámite
                                                    </h2>
                                                    
                                                    <!-- Detail items -->
                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td style="font-weight: 600; color: #4a5568; font-size: 15px;">Factura:</td>
                                                                        <td align="right" style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $datosTramite['factura'] }}</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td style="font-weight: 600; color: #4a5568; font-size: 15px;">Producto:</td>
                                                                        <td align="right" style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $datosTramite['nombre_producto'] }}</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td style="font-weight: 600; color: #4a5568; font-size: 15px;">No. Económico:</td>
                                                                        <td align="right" style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $datosTramite['no_economico'] }}</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 15px 0;">
                                                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                    <tr>
                                                                        <td style="font-weight: 600; color: #4a5568; font-size: 15px;">No. Alpha:</td>
                                                                        <td align="right" style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $datosTramite['no_alpha'] }}</td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- SUPPORT SECTION -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #ebf8ff; border-radius: 8px; border-left: 4px solid #3b82f6; margin-top: 30px;">
                                            <tr>
                                                <td style="padding: 25px;">
                                                    <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #2d3748; font-weight: 600;">
                                                        ¿Necesita ayuda?
                                                    </h3>
                                                    
                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="padding: 5px 0; font-size: 15px; color: #4a5568;">
                                                                📧 <a href="mailto:ventas@crosspoint.com" style="color: #3b82f6; text-decoration: none;">ventas@crosspoint.com</a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 5px 0; font-size: 15px; color: #4a5568;">
                                                                📞 <a href="tel:+528991610219" style="color: #3b82f6; text-decoration: none;">+52 899 161 0219</a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 5px 0; font-size: 15px; color: #4a5568;">
                                                                🕒 Lun-Vie: 6:00 - 20:00
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 5px 0; font-size: 15px; color: #4a5568;">
                                                                🕒 Sab-Dom: 6:00 - 14:00
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <p style="margin: 30px 0 15px 0; font-size: 16px; color: #4a5568;">
                                            Agradecemos su confianza en nuestros servicios. Estaremos atentos a cualquier consulta adicional.
                                        </p>
                                        
                                        <p style="margin: 0; font-size: 17px; color: #1a202c; font-weight: 700;">
                                            Atentamente,<br>El equipo de Crosspoint.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- FOOTER -->
                    <tr>
                        <td style="padding: 35px 30px; text-align: center; border-top: 1px solid #e2e8f0; background-color: #f7fafc;">
                            <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: 700; color: #2d3748;">
                                Crosspoint
                            </p>
                            
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #718096;">
                                Carretera Reynosa a Puente Pharr No.400 Loc. 24<br>
                                Ejido el Guerreño, Reynosa Tamaulipas, 88780
                            </p>
                            
                            <p style="margin: 0 0 20px 0; font-size: 14px; color: #718096;">
                                <a href="mailto:ventas@crosspoint.com" style="color: #3b82f6; text-decoration: none;">ventas@crosspoint.com</a> | 
                                <a href="tel:+528991610219" style="color: #3b82f6; text-decoration: none;">+52 899 161 0219</a>
                            </p>
                            
                            <p style="margin: 20px 0 0 0; font-size: 12px; color: #a0aec0;">
                                © 2025 Crosspoint. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    
</body>
</html>