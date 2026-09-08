<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;

/**
 * Controlador para el Módulo de Reportes de Consultas del Chatbot.
 * Accesible por roles Administrador y Supervisor.
 */
class ReporteController extends Controller
{
    /**
     * @var Reporte
     */
    private $reporteModel;

    public function __construct()
    {
        $this->reporteModel = new Reporte();
    }

    /**
     * Muestra la vista de visualización y filtrado de consultas paginadas.
     * GET /reportes/visualizar
     */
    public function visualizar()
    {
        // 1. Capturar parámetros de filtrado desde GET
        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fechaFin    = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $idTipo      = isset($_GET['id_tipo']) && $_GET['id_tipo'] !== '' ? (int)$_GET['id_tipo'] : null;
        $buscar      = isset($_GET['buscar']) && $_GET['buscar'] !== '' ? trim($_GET['buscar']) : null;

        $filtros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'id_tipo'      => $idTipo,
            'buscar'       => $buscar,
        ];

        // 2. Parámetros de paginación
        $pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($pagina < 1) {
            $pagina = 1;
        }

        $limit = 10;
        $offset = ($pagina - 1) * $limit;

        // 3. Consultar datos al modelo
        $tiposConsulta   = $this->reporteModel->getTiposConsulta();
        $totalRegistros  = $this->reporteModel->getTotalConsultas($filtros);
        $totalPaginas    = (int)ceil($totalRegistros / $limit);
        if ($totalPaginas < 1) {
            $totalPaginas = 1;
        }

        $consultas = $this->reporteModel->getConsultasPaginadas($filtros, $limit, $offset);

        // 4. Renderizar la vista
        $this->view('reportes/visualizar', [
            'consultas'      => $consultas,
            'tiposConsulta'  => $tiposConsulta,
            'filtros'        => $filtros,
            'pagina'         => $pagina,
            'totalPaginas'   => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'limit'          => $limit,
        ]);
    }

    /**
     * Exporta las consultas filtradas a formato CSV.
     * GET /reportes/exportar
     */
    public function exportar()
    {
        // Capturar filtros (sin paginación)
        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fechaFin    = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $idTipo      = isset($_GET['id_tipo']) && $_GET['id_tipo'] !== '' ? (int)$_GET['id_tipo'] : null;
        $buscar      = isset($_GET['buscar']) && $_GET['buscar'] !== '' ? trim($_GET['buscar']) : null;

        $filtros = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'id_tipo'      => $idTipo,
            'buscar'       => $buscar,
        ];

        $consultas = $this->reporteModel->getAllConsultasExport($filtros);

        // Configurar cabeceras para forzar descarga de archivo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_consultas_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 para reconocimiento correcto de tildes y caracteres especiales en Excel
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados del CSV
        fputcsv($output, ['ID Consulta', 'Cód. Socio', 'Nombres', 'Tipo', 'Fecha', 'Hora', 'Atendido por', 'Descripción', 'Ubicación', 'Zona', 'Ruta', 'Glosa', 'Coordenadas GPS']);

        // Escribir filas de datos
        foreach ($consultas as $row) {
            fputcsv($output, [
                $row['id_consulta'],
                $row['codigo_socio'],
                $row['nombres'],
                $row['tipo'],
                $row['fecha_consulta'],
                $row['hora_consulta'],
                !empty($row['username']) ? $row['username'] : 'Chatbot',
                $row['descripcion'],
                $row['ubicacion'],
                $row['zona'],
                $row['ruta'],
                $row['glosa'],
                $row['coordenadas_gps']
            ]);
        }

        fclose($output);
        exit;
    }
}
