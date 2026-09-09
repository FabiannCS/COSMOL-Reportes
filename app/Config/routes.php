<?php

return [
    'GET' => [
        // Rutas Públicas
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
        '/logout' => ['AuthController', 'logout', ['auth']],

        // Panel Principal (Dashboard)
        '/dashboard' => ['AuthController', 'dashboard', ['auth', 'role:Administrador,Supervisor', 'permission:reportes.ver,trabajos.ver,usuarios.ver,roles.ver']],

        // Seguridad - Roles y Permisos
        '/seguridad/roles'    => ['RolController', 'index', ['auth', 'role:Administrador', 'permission:roles.ver']],
        '/seguridad/permisos' => ['RolController', 'permisos', ['auth', 'role:Administrador', 'permission:roles.permisos']],

        // Operador
        '/operador/trabajos' => ['OperadorController', 'trabajos', ['auth', 'permission:trabajos.ver']],
        '/operador/detalle'  => ['OperadorController', 'detalle', ['auth', 'permission:trabajos.ver']],
        '/operador/historial'=> ['OperadorController', 'historial', ['auth', 'permission:trabajos.ver']],

        // Perfil General (Todos los usuarios)
        '/perfil' => ['PerfilController', 'index', ['auth']],

        // Administrador / Supervisión
        '/administrador/usuarios'         => ['UsuarioController', 'index',                ['auth', 'role:Administrador,Supervisor', 'permission:usuarios.ver']],
        '/administrador/trabajos'         => ['AdministradorController', 'trabajos',       ['auth', 'role:Administrador,Supervisor', 'permission:trabajos.ver']],
        '/administrador/trabajos/detalle' => ['AdministradorController', 'trabajoDetalle', ['auth', 'role:Administrador,Supervisor', 'permission:trabajos.ver']],
        '/administrador/historial'        => ['AdministradorController', 'historial',      ['auth', 'role:Administrador,Supervisor', 'permission:trabajos.ver']],

        // Módulo de Reportes
        '/reportes/visualizar' => ['ReporteController', 'visualizar', ['auth', 'permission:reportes.ver']],
        '/reportes/exportar'   => ['ReporteController', 'exportar',   ['auth', 'permission:reportes.exportar']],
    ],
    'POST' => [
        // Autenticación
        '/login' => ['AuthController', 'login', ['csrf']],

        // Acciones de Roles y Permisos
        '/seguridad/roles/crear'    => ['RolController', 'store',           ['auth', 'permission:roles.crear', 'csrf']],
        '/seguridad/roles/editar'   => ['RolController', 'update',          ['auth', 'permission:roles.editar', 'csrf']],
        '/seguridad/roles/permisos' => ['RolController', 'guardarPermisos', ['auth', 'permission:roles.permisos', 'csrf']],

        // Operador
        '/operador/concluir' => ['OperadorController', 'concluir', ['auth', 'permission:trabajos.concluir', 'csrf']],

        // Administrador
        '/administrador/trabajos/concluir' => ['AdministradorController', 'concluir', ['auth', 'permission:trabajos.concluir', 'csrf']],

        // Acciones de Usuarios
        '/administrador/usuarios/crear'  => ['UsuarioController', 'store',        ['auth', 'permission:usuarios.crear', 'csrf']],
        '/administrador/usuarios/editar' => ['UsuarioController', 'update',       ['auth', 'permission:usuarios.editar', 'csrf']],
        '/administrador/usuarios/estado' => ['UsuarioController', 'toggleEstado', ['auth', 'permission:usuarios.estado', 'csrf']],

        // API
        '/api/consultas' => ['ConsultaApiController', 'registrar', []],
    ],
];
