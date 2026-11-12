/**
 * COSMOL Reportes — JavaScript Base
 * Manejo de interacciones del dashboard (Sidebar plegable/mini, tooltips Bootstrap, responsive backdrop)
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('appSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const sidebarToggleBtn = document.getElementById('sidebarToggle'); // Botón móvil en barra superior
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn'); // Botón escritorio dentro del sidebar
    const sidebarCloseMobileBtn = document.getElementById('sidebarCloseMobileBtn'); // Botón de cierre móvil dentro del sidebar

    // Inicializar tooltips de Bootstrap 5 para los elementos del sidebar
    const tooltipElements = document.querySelectorAll('#appSidebar [data-bs-toggle="tooltip"]');
    const sidebarTooltips = [];

    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipElements.forEach(function (el) {
            try {
                const tooltipInstance = new bootstrap.Tooltip(el, {
                    trigger: 'hover',
                    boundary: 'window',
                    customClass: 'sidebar-tooltip'
                });
                sidebarTooltips.push({ el: el, instance: tooltipInstance });

                // Ocultar tooltip inmediatamente al hacer clic
                el.addEventListener('click', function () {
                    tooltipInstance.hide();
                });
            } catch (err) {
                console.warn('Error inicializando tooltip en sidebar:', err);
            }
        });
    }

    function isDesktopView() {
        return window.innerWidth >= 992;
    }

    // Actualizar activación y textos de tooltips según el estado del sidebar
    function updateTooltipsState() {
        const isDesktop = isDesktopView();
        const isCollapsed = document.body.classList.contains('sidebar-collapsed');

        // Actualizar tooltip del botón de colapso
        if (sidebarCollapseBtn) {
            const collapseTooltip = bootstrap.Tooltip.getInstance(sidebarCollapseBtn);
            const newTitle = isCollapsed ? 'Expandir menú lateral' : 'Plegar menú lateral';
            sidebarCollapseBtn.setAttribute('data-bs-title', newTitle);
            sidebarCollapseBtn.setAttribute('title', newTitle);
            if (collapseTooltip) {
                collapseTooltip.setContent({ '.tooltip-inner': newTitle });
            }
        }

        // Los tooltips de los ítems de navegación solo se activan cuando está colapsado en escritorio
        sidebarTooltips.forEach(function (item) {
            // No deshabilitar el botón de colapso
            if (item.el === sidebarCollapseBtn) {
                item.instance.enable();
                return;
            }

            if (isDesktop && isCollapsed) {
                item.instance.enable();
            } else {
                item.instance.disable();
                item.instance.hide();
            }
        });
    }

    // Alternar modo colapsado (mini sidebar) en escritorio
    function toggleDesktopCollapse() {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        try {
            localStorage.setItem('cosmol_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        } catch (e) {
            // Ignorar errores de almacenamiento local
        }
        updateTooltipsState();
    }

    // Control del menú móvil (offcanvas)
    function openMobileSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (backdrop) backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (backdrop) backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Evento: Botón interno del sidebar para colapsar en escritorio
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            toggleDesktopCollapse();
        });
    }

    // Evento: Botón hamburguesa en barra superior (móvil)
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (isDesktopView()) {
                toggleDesktopCollapse();
            } else {
                if (sidebar && sidebar.classList.contains('show')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
        });
    }

    // Evento: Botón cerrar dentro del sidebar en móvil
    if (sidebarCloseMobileBtn) {
        sidebarCloseMobileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeMobileSidebar();
        });
    }

    // Evento: Clic en el telón de fondo (móvil)
    if (backdrop) {
        backdrop.addEventListener('click', function () {
            closeMobileSidebar();
        });
    }

    // Cerrar sidebar móvil con la tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeMobileSidebar();
        }
    });

    // Control al redimensionar ventana
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (isDesktopView()) {
                closeMobileSidebar();
            }
            updateTooltipsState();
        }, 100);
    });

    // Restauración de sesión desde bfcache
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            closeMobileSidebar();
            updateTooltipsState();
        }
    });

    // Sincronizar estado inicial de tooltips
    updateTooltipsState();
});
