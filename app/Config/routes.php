<?php

return [
    'GET' => [
        // Rutas Públicas
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
        '/logout' => ['AuthController', 'logout', ['auth']],

        // Panel Principal (Dashboard) - Admin y Supervisor
        '/dashboard' => ['AuthController', 'dashboard', ['auth', 'role:Administrador,Supervisor']],

        // Seguridad - Roles y Permisos (Solo Administrador)
        '/seguridad/roles' => ['RolController', 'index', ['auth', 'role:Administrador']],
        '/seguridad/permisos' => ['RolController', 'permisos', ['auth', 'role:Administrador']],

        // Operador
        '/operador/trabajos' => ['OperadorController', 'trabajos', ['auth', 'role:Operador']],
        '/operador/detalle'  => ['OperadorController', 'detalle', ['auth', 'role:Operador']],

        // Administrador
        '/administrador/usuarios'         => ['UsuarioController', 'index',                ['auth', 'role:Administrador']],
        '/administrador/trabajos'         => ['AdministradorController', 'trabajos',       ['auth', 'role:Administrador']],
        '/administrador/trabajos/detalle' => ['AdministradorController', 'trabajoDetalle', ['auth', 'role:Administrador']],
    ],
    'POST' => [
        // Autenticación
        '/login' => ['AuthController', 'login', []],

        // Acciones de Roles y Permisos (Solo Administrador)
        '/seguridad/roles/crear' => ['RolController', 'store', ['auth', 'role:Administrador']],
        '/seguridad/roles/editar' => ['RolController', 'update', ['auth', 'role:Administrador']],
        '/seguridad/roles/permisos' => ['RolController', 'guardarPermisos', ['auth', 'role:Administrador']],

        // Operador
        '/operador/concluir' => ['OperadorController', 'concluir', ['auth', 'role:Operador']],

        // Acciones de Usuarios (Solo Administrador)
        '/administrador/usuarios/crear' => ['UsuarioController', 'store', ['auth', 'role:Administrador']],
        '/administrador/usuarios/editar' => ['UsuarioController', 'update', ['auth', 'role:Administrador']],
        '/administrador/usuarios/estado' => ['UsuarioController', 'toggleEstado', ['auth', 'role:Administrador']],
    ],
];
