<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;

/**
 * Controlador para el Módulo de Reportes de Consultas del Chatbot y App Móvil.
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
     * Muestra la vista de visualización y filtrado de consultas del Chatbot.
     * Soporta dos vistas:
     *  - 'socios' (por defecto): Agrupada por socio/usuario único con desglose de acciones.
     *  - 'acciones': Registro detallado de cada acción individual (sin ubicación).
     * GET /reportes/visualizar
     */
    public function visualizar()
    {
        // 1. Determinar modo de vista (por defecto 'socios', o 'acciones')
        $vista = isset($_GET['vista']) && $_GET['vista'] === 'acciones' ? 'acciones' : 'socios';

        // 2. Capturar parámetros de filtrado desde GET
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

        // 3. Parámetros de paginación
        $pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($pagina < 1) {
            $pagina = 1;
        }

        $limit = 15;
        $offset = ($pagina - 1) * $limit;

        // Métricas globales
        $tiposConsulta   = $this->reporteModel->getTiposConsulta();
        $totalesPorTipo  = $this->reporteModel->getTotalesPorTipoConsulta($filtros);
        $totalConsultas  = $this->reporteModel->getTotalConsultas($filtros);
        $totalSocios     = $this->reporteModel->getTotalSociosChatbot($filtros);

        $consultas        = [];
        $socios           = [];
        $accionesPorSocio = [];

        if ($vista === 'socios') {
            $totalRegistros = $totalSocios;
            $totalPaginas   = (int)ceil($totalRegistros / $limit);
            if ($totalPaginas < 1) {
                $totalPaginas = 1;
            }

            $socios = $this->reporteModel->getSociosChatbotPaginados($filtros, $limit, $offset);
            $socioKeys = array_column($socios, 'socio_key');
            if (!empty($socioKeys)) {
                $accionesPorSocio = $this->reporteModel->getAccionesChatbotPorSocios($socioKeys, $filtros);
            }
        } else {
            $totalRegistros = $totalConsultas;
            $totalPaginas   = (int)ceil($totalRegistros / $limit);
            if ($totalPaginas < 1) {
                $totalPaginas = 1;
            }

            $consultas = $this->reporteModel->getConsultasPaginadas($filtros, $limit, $offset);
        }

        // 4. Renderizar la vista
        $this->view('reportes/visualizar', [
            'vista'            => $vista,
            'socios'           => $socios,
            'accionesPorSocio' => $accionesPorSocio,
            'consultas'        => $consultas,
            'tiposConsulta'    => $tiposConsulta,
            'totalesPorTipo'   => $totalesPorTipo,
            'filtros'          => $filtros,
            'pagina'           => $pagina,
            'totalPaginas'     => $totalPaginas,
            'totalRegistros'   => $totalRegistros,
            'totalSocios'      => $totalSocios,
            'totalConsultas'   => $totalConsultas,
            'limit'            => $limit,
        ]);
    }

    /**
     * Exporta los datos filtrados a formato CSV (respetando la vista activa).
     * GET /reportes/exportar
     */
    public function exportar()
    {
        $vista = isset($_GET['vista']) && $_GET['vista'] === 'acciones' ? 'acciones' : 'socios';

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

        header('Content-Type: text/csv; charset=utf-8');

        if ($vista === 'socios') {
            header('Content-Disposition: attachment; filename="reporte_chatbot_socios_' . date('Ymd_His') . '.csv"');
            $socios = $this->reporteModel->getAllSociosChatbotExport($filtros);

            $output = fopen('php://output', 'w');
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($output, ['#', 'Cód. Socio', 'Nombres / Titular', 'Teléfono WhatsApp', 'Total Consultas', 'Última Fecha', 'Última Hora']);

            $num = 1;
            foreach ($socios as $row) {
                fputcsv($output, [
                    $num++,
                    !empty($row['codigo_socio']) ? $row['codigo_socio'] : 'N/A',
                    !empty($row['nombres']) ? $row['nombres'] : 'Desconocido',
                    !empty($row['telefono']) ? $row['telefono'] : 'N/A',
                    $row['total_consultas'],
                    $row['ultima_fecha'],
                    $row['ultima_hora']
                ]);
            }

            fclose($output);
            exit;
        } else {
            header('Content-Disposition: attachment; filename="reporte_chatbot_acciones_' . date('Ymd_His') . '.csv"');
            $consultas = $this->reporteModel->getAllConsultasExport($filtros);

            $output = fopen('php://output', 'w');
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // Encabezados limpios - SIN Ubicación
            fputcsv($output, ['ID Consulta', 'Cód. Socio', 'Nombres', 'Teléfono WhatsApp', 'Tipo de Consulta', 'Fecha', 'Hora', 'Atendido por']);

            foreach ($consultas as $row) {
                fputcsv($output, [
                    $row['id_consulta'],
                    $row['codigo_socio'],
                    $row['nombres'],
                    !empty($row['telefono']) ? $row['telefono'] : 'N/A',
                    $row['tipo'],
                    $row['fecha_consulta'],
                    $row['hora_consulta'],
                    !empty($row['username']) ? $row['username'] : 'Chatbot'
                ]);
            }

            fclose($output);
            exit;
        }
    }

    /**
     * Muestra la vista de visualización y filtrado de consultas de la App Móvil.
     * Soporta dos vistas:
     *  - 'socios' (por defecto): Agrupada por socio/usuario único con desglose de eventos.
     *  - 'acciones': Registro detallado de cada evento individual.
     * GET /reportes/app-movil
     */
    public function visualizarAppMovil()
    {
        $vista = isset($_GET['vista']) && $_GET['vista'] === 'acciones' ? 'acciones' : 'socios';

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

        $limit = 15;
        $offset = ($pagina - 1) * $limit;

        // 3. Consultar datos al modelo
        $tiposConsulta   = $this->reporteModel->getTiposConsultaApp();
        $totalesPorTipo  = $this->reporteModel->getTotalesPorTipoApp($filtros);
        $totalConsultas  = $this->reporteModel->getTotalConsultasApp($filtros);
        $totalSocios     = $this->reporteModel->getTotalSociosApp($filtros);

        $consultas        = [];
        $socios           = [];
        $accionesPorSocio = [];

        if ($vista === 'socios') {
            $totalRegistros = $totalSocios;
            $totalPaginas   = (int)ceil($totalRegistros / $limit);
            if ($totalPaginas < 1) {
                $totalPaginas = 1;
            }

            $socios = $this->reporteModel->getSociosAppPaginados($filtros, $limit, $offset);
            $socioKeys = array_column($socios, 'socio_key');
            if (!empty($socioKeys)) {
                $accionesPorSocio = $this->reporteModel->getAccionesAppPorSocios($socioKeys, $filtros);
            }
        } else {
            $totalRegistros = $totalConsultas;
            $totalPaginas   = (int)ceil($totalRegistros / $limit);
            if ($totalPaginas < 1) {
                $totalPaginas = 1;
            }

            $consultas = $this->reporteModel->getConsultasAppPaginadas($filtros, $limit, $offset);
        }

        // 4. Renderizar la vista
        $this->view('reportes/app_movil', [
            'vista'            => $vista,
            'socios'           => $socios,
            'accionesPorSocio' => $accionesPorSocio,
            'consultas'        => $consultas,
            'tiposConsulta'    => $tiposConsulta,
            'totalesPorTipo'   => $totalesPorTipo,
            'filtros'          => $filtros,
            'pagina'           => $pagina,
            'totalPaginas'     => $totalPaginas,
            'totalRegistros'   => $totalRegistros,
            'totalSocios'      => $totalSocios,
            'totalConsultas'   => $totalConsultas,
            'limit'            => $limit,
        ]);
    }

    /**
     * Exporta los datos filtrados de la App Móvil a formato CSV (respetando la vista activa).
     * GET /reportes/app-movil/exportar
     */
    public function exportarAppMovil()
    {
        $vista = isset($_GET['vista']) && $_GET['vista'] === 'acciones' ? 'acciones' : 'socios';

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

        header('Content-Type: text/csv; charset=utf-8');

        if ($vista === 'socios') {
            header('Content-Disposition: attachment; filename="reporte_app_movil_socios_' . date('Ymd_His') . '.csv"');
            $socios = $this->reporteModel->getAllSociosAppExport($filtros);

            $output = fopen('php://output', 'w');
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($output, ['#', 'Cód. Socio', 'Nombres / Titular', 'Teléfono', 'Total Eventos', 'Última Fecha', 'Última Hora', 'Canal']);

            $num = 1;
            foreach ($socios as $row) {
                fputcsv($output, [
                    $num++,
                    !empty($row['codigo_socio']) ? $row['codigo_socio'] : 'N/A',
                    !empty($row['nombres']) ? $row['nombres'] : 'Desconocido',
                    !empty($row['telefono']) ? $row['telefono'] : 'N/A',
                    $row['total_consultas'],
                    $row['ultima_fecha'],
                    $row['ultima_hora'],
                    'App Móvil'
                ]);
            }

            fclose($output);
            exit;
        } else {
            header('Content-Disposition: attachment; filename="reporte_app_movil_acciones_' . date('Ymd_His') . '.csv"');
            $consultas = $this->reporteModel->getAllConsultasAppExport($filtros);

            $output = fopen('php://output', 'w');
            fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // Encabezados limpios sin ubicación
            fputcsv($output, ['ID Consulta', 'Cód. Socio', 'Nombres', 'Teléfono', 'Tipo de Evento', 'Fecha', 'Hora', 'Canal']);

            foreach ($consultas as $row) {
                fputcsv($output, [
                    $row['id_consulta'],
                    $row['codigo_socio'],
                    $row['nombres'],
                    !empty($row['telefono']) ? $row['telefono'] : 'N/A',
                    $row['tipo'],
                    $row['fecha_consulta'],
                    $row['hora_consulta'],
                    'App Móvil'
                ]);
            }

            fclose($output);
            exit;
        }
    }
}
