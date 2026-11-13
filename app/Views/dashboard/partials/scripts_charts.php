<?php
/**
 * Componente: Scripts de Gráficos Interactivos (Chart.js v4)
 * Archivo: app/Views/dashboard/partials/scripts_charts.php
 * 
 * Inicialización de gráficos de evolución, balance operativo, trámites y personal.
 * 
 * @var array $labelsDias
 * @var array $dataWhatsAppDias
 * @var array $dataAppMovilDias
 * @var int $totalConcluidos
 * @var int $totalPendientes
 * @var int $totalNoConcluidos
 * @var array $labelsTipos
 * @var array $dataTipos
 * @var int $recPendCount
 * @var int $reclAlcantarilladoPend
 * @var int $reclAguaPotablePend
 * @var array $mapOpPorEsp
 */
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tipografía y colores globales de Chart.js acordes a DESIGN.md
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
    Chart.defaults.plugins.tooltip.titleColor = '#ffffff';
    Chart.defaults.plugins.tooltip.bodyColor = '#f8fafc';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;

    // --- GRÁFICO 1: Evolución Diaria de Consultas por Canal (Line/Area) ---
    const ctxEvolucion = document.getElementById('chartEvolucionCanales');
    if (ctxEvolucion) {
        new Chart(ctxEvolucion, {
            type: 'line',
            data: {
                labels: <?= json_encode($labelsDias) ?>,
                datasets: [
                    {
                        label: 'Chatbot WhatsApp',
                        data: <?= json_encode($dataWhatsAppDias) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.10)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'App Móvil',
                        data: <?= json_encode($dataAppMovilDias) ?>,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11, weight: '500' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            precision: 0,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // --- GRÁFICO 2: Balance Operativo de Trabajos (Doughnut) ---
    const ctxBalance = document.getElementById('chartBalanceOperativo');
    if (ctxBalance) {
        new Chart(ctxBalance, {
            type: 'doughnut',
            data: {
                labels: ['Concluidos', 'Pendientes'<?= $totalNoConcluidos > 0 ? ", 'Contingencias'" : "" ?>],
                datasets: [{
                    data: [
                        <?= (int)$totalConcluidos ?>,
                        <?= (int)$totalPendientes ?><?= $totalNoConcluidos > 0 ? ", " . (int)$totalNoConcluidos : "" ?>
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b'<?= $totalNoConcluidos > 0 ? ", '#ef4444'" : "" ?>
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const val = context.raw || 0;
                                const total = <?= (int)($totalConcluidos + $totalPendientes + $totalNoConcluidos) ?>;
                                const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // --- GRÁFICO 3: Top Consultas y Trámites Frecuentes (Horizontal Bar) ---
    const ctxTop = document.getElementById('chartTopConsultas');
    if (ctxTop) {
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsTipos) ?>,
                datasets: [{
                    label: 'Consultas Registradas',
                    data: <?= json_encode($dataTipos) ?>,
                    backgroundColor: 'rgba(2, 132, 199, 0.85)',
                    hoverBackgroundColor: '#0369a1',
                    borderRadius: 4,
                    borderSkipped: false,
                    barPercentage: 0.65
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { precision: 0, font: { size: 11 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#334155' }
                    }
                }
            }
        });
    }

    // --- GRÁFICO 4: Carga de Trabajo vs Operadores (Grouped Bar) ---
    const ctxCarga = document.getElementById('chartCargaOperadores');
    if (ctxCarga) {
        new Chart(ctxCarga, {
            type: 'bar',
            data: {
                labels: ['Reconexión', 'Alcantarillado', 'Agua Potable'],
                datasets: [
                    {
                        label: 'Órdenes Pendientes',
                        data: [
                            <?= (int)$recPendCount ?>,
                            <?= (int)$reclAlcantarilladoPend ?>,
                            <?= (int)$reclAguaPotablePend ?>
                        ],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.65
                    },
                    {
                        label: 'Operadores Disponibles',
                        data: [
                            <?= (int)($mapOpPorEsp['Reconexión'] ?? 0) ?>,
                            <?= (int)($mapOpPorEsp['Maestro de alcantarillado'] ?? 0) ?>,
                            <?= (int)($mapOpPorEsp['Agua Potable'] ?? 0) ?>
                        ],
                        backgroundColor: '#0284c7',
                        borderRadius: 4,
                        barPercentage: 0.65
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11.5, weight: '500' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11.5, weight: '500' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { precision: 0, font: { size: 11 } }
                    }
                }
            }
        });
    }

    // --- GRÁFICO 5: Tiempo Promedio por Servicio (Bar) ---
    const ctxTiemposServ = document.getElementById('chartTiemposServicio');
    if (ctxTiemposServ) {
        new Chart(ctxTiemposServ, {
            type: 'bar',
            data: {
                labels: <?= json_encode(isset($labelsTiemposServicio) ? $labelsTiemposServicio : []) ?>,
                datasets: [{
                    label: 'Horas Promedio',
                    data: <?= json_encode(isset($dataTiemposServicio) ? $dataTiemposServicio : []) ?>,
                    backgroundColor: ['#f59e0b', '#0284c7', '#10b981', '#64748b'],
                    borderRadius: 4,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const textos = <?= json_encode(isset($textosTiemposServicio) ? $textosTiemposServicio : []) ?>;
                                const valHoras = context.parsed.y !== null ? context.parsed.y : 0;
                                const textoLegible = textos[context.dataIndex] || '';
                                return ` Promedio: ${valHoras} hrs (${textoLegible})`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(v) { return v + ' h'; },
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // --- GRÁFICO 6: Tiempo Promedio de Atención por Técnico (Horizontal Bar) ---
    const ctxTiemposTec = document.getElementById('chartTiemposTecnicos');
    if (ctxTiemposTec) {
        new Chart(ctxTiemposTec, {
            type: 'bar',
            data: {
                labels: <?= json_encode(isset($labelsTiemposTecnicos) ? $labelsTiemposTecnicos : []) ?>,
                datasets: [{
                    label: 'Horas Promedio',
                    data: <?= json_encode(isset($dataTiemposTecnicos) ? $dataTiemposTecnicos : []) ?>,
                    backgroundColor: '#0d9488',
                    hoverBackgroundColor: '#0f766e',
                    borderRadius: 4,
                    barPercentage: 0.65
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const textos = <?= json_encode(isset($textosTiemposTecnicos) ? $textosTiemposTecnicos : []) ?>;
                                const casos  = <?= json_encode(isset($casosTiemposTecnicos) ? $casosTiemposTecnicos : []) ?>;
                                const valHoras = context.parsed.x !== null ? context.parsed.x : 0;
                                const textoLegible = textos[context.dataIndex] || '';
                                const totalCasos = casos[context.dataIndex] || 0;
                                return ` Promedio: ${valHoras} hrs (${textoLegible}) • ${totalCasos} trabajos`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(v) { return v + ' h'; },
                            font: { size: 11 }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    }
                }
            }
        });
    }
});
</script>
