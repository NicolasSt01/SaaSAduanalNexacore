@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4>Importar Operaciones desde Excel</h4>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Instrucciones:</strong>
                            <ul class="mb-0">
                                <li>El archivo debe ser formato .xlsx o .xls</li>
                                <li>Los encabezados deben estar en la fila 1</li>
                                <li>Asegúrate de que los nombres de clientes, importadores, bodegas, aduanas y patentes
                                    coincidan con los registrados en el sistema</li>
                                <li>Los registros sin número de factura, con referencias duplicadas y sin numero de pedimento previamente registrados serán omitidos</li>
                            </ul>
                        </div>

                        <form id="formImportar" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="archivo">Seleccionar archivo Excel</label>
                                <input type="file" class="form-control-file" id="archivo" name="archivo" accept=".xlsx,.xls"
                                    required>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3" id="btnImportar">
                                <i class="fas fa-upload"></i> Importar Archivo
                            </button>

                            <a href="{{ route('operaciones.index') }}" class="btn btn-secondary mt-3">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </form>

                        <!-- Área de resultados -->
                        <div id="resultadosContainer" class="mt-4" style="display: none;">
                            <hr>
                            <h5>Resultados de la Importación</h5>

                            <div class="alert alert-success" id="resumenExito">
                                <strong>Registros importados exitosamente:</strong> <span id="exitososCount">0</span>
                            </div>

                            <div class="alert alert-warning" id="resumenOmitidos" style="display: none;">
                                <strong>Registros omitidos:</strong> <span id="omitidosCount">0</span>
                            </div>

                            <div class="form-group">
                                <label for="logTextarea"><strong>Detalle de errores:</strong></label>
                                <textarea class="form-control" id="logTextarea" rows="10" readonly></textarea>
                            </div>

                            <button type="button" class="btn btn-success" id="btnDescargarLog">
                                <i class="fas fa-download"></i> Descargar Log Completo
                            </button>
                        </div>

                        <!-- Spinner de carga -->
                        <div id="spinnerContainer" class="text-center mt-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Procesando...</span>
                            </div>
                            <p class="mt-2">Procesando archivo, por favor espere...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            let logFileName = null;

            $('#formImportar').on('submit', function (e) {
                e.preventDefault();

                // Ocultar resultados anteriores
                $('#resultadosContainer').hide();
                $('#spinnerContainer').show();

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('operaciones.import.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#spinnerContainer').hide();

                        if (response.success) {
                            // Mostrar resultados
                            $('#exitososCount').text(response.exitosos);
                            $('#omitidosCount').text(response.omitidos);

                            if (response.omitidos > 0) {
                                $('#resumenOmitidos').show();

                                // Mostrar errores en el textarea
                                let erroresTexto = response.errores.join('\n');
                                $('#logTextarea').val(erroresTexto);
                            } else {
                                $('#resumenOmitidos').hide();
                                $('#logTextarea').val('No hay errores que reportar.');
                            }

                            // Guardar nombre del archivo log
                            logFileName = response.log_file;

                            $('#resultadosContainer').show();

                            // Mostrar notificación de éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Importación completada!',
                                text: `${response.exitosos} registros importados, ${response.omitidos} omitidos.`,
                                confirmButtonText: 'Aceptar'
                            });

                            // Limpiar formulario
                            $('#formImportar')[0].reset();
                        }
                    },
                    error: function (xhr) {
                        $('#spinnerContainer').hide();

                        console.log('Error completo:', xhr.responseJSON);

                        let mensaje = 'Error desconocido';
                        let detalles = '';

                        if (xhr.responseJSON) {
                            mensaje = xhr.responseJSON.mensaje || 'Error al procesar';
                            detalles = 'Línea: ' + (xhr.responseJSON.linea || 'N/A') + '\n';
                            detalles += 'Archivo: ' + (xhr.responseJSON.archivo_error || 'N/A');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: '<strong>Mensaje:</strong><br>' + mensaje + '<br><br><pre>' + detalles + '</pre>',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            });

            // Descargar log
            $('#btnDescargarLog').on('click', function () {
                if (logFileName) {
                    window.location.href = "{{ url('/operaciones/importar/log') }}/" + logFileName;
                }
            });
        });
    </script>
@endsection

@section('scripts')

@endsection