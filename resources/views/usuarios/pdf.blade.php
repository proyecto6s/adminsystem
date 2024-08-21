<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte general de Usuarios</title>
    <!-- Estilo ajustado -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            margin-top: 20px;
            position: relative;
            text-align: center;
        }
        .logo {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 120px;
            height: 120px;
        }
        .report-details {
            margin-top: 10px;
        }
        .report-details h3 {
            margin-bottom: 0;
        }
        .report-details p {
            margin: 4px 0;
        }
        .content {
            margin: 10px;
            margin-top: 60px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 9px;
        }
        .cabecera {
            background-color: #343a40;
            color: white;
        }
        .table, .table th, .table td {
            border: 1px solid black;
        }
        .table th, .table td {
            padding: 4px;
            text-align: center;
        }
        .footer {
            width: 100%;
            position: fixed;
            bottom: 20px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            padding: 0 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-details">
            <h3>Reporte general de Usuarios</h3>
            <p>Constructora Traterra S. de R.L</p>
        </div>
        <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
    </div>
    
    <div class="content">
        <table class="table table-striped">
            <thead class="cabecera">
                <tr>
                    <th>Usuario</th>
                    <th>Nombre Usuario</th>
                    <th>Estado Usuario</th>
                    <th>Rol</th>
                    <th>Fecha Última Conexión</th>
                    <th>Cantidad Ingresos</th>
                    <th>Fecha Vencimiento</th>
                    <th>Correo Electrónico</th>
                </tr>
            </thead>
            <tbody>
                @if(is_array($usuarios) || is_object($usuarios))
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario['Usuario'] }}</td>
                            <td>{{ $usuario['Nombre_Usuario'] }}</td>
                            <td> @if($usuario->estado)
                                {{ $usuario->estado->ESTADO }}
                            @else
                                {{ $usuario->Estado_Usuario }}
                            @endif</td>
                            <td>{{ $usuario->rol->Rol }}</td>
                            <td>{{ \Carbon\Carbon::parse($usuario['Fecha_Ultima_Conexion'])->format('Y/m/d') }}</td>
                            <td>{{ $usuario['Primer_Ingreso'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($usuario['Fecha_Vencimiento'])->format('Y/m/d') }}</td>
                            <td>{{ $usuario['Correo_Electronico'] }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8">No se encontraron usuarios.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="footer">
        <script type="text/php">
            if ( isset($pdf) ) {
                $pdf->page_script('
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                    $y = 820;  // Ajusta la posición y para estar en la parte inferior
                    $x = 520;  // Ajusta la posición x para estar en el borde derecho
                    $pdf->text($x, $y, $pageText, $font, $size);
                ');
            }
        </script>
    </div>

    <div class="footer">
        <script type="text/php">
            if ( isset($pdf) ) {
                $pdf->page_script('
                    $font = $fontMetrics->get_font("Arial", "normal");
                    $size = 10;
                    $pageText = "Página " . $PAGE_NUM . " de " . $PAGE_COUNT;
                    $pdf->text(520, 820, $pageText, $font, $size);
                    $pdf->text(30, 820, "{{ $fechaHora }}", $font, $size);
                ');
            }
        </script>
    </div>
</body>
</html>
