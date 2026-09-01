<?php

return [
    'GET' => [
        // Rutas Públicas
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
        '/logout' => ['AuthController', 'logout', ['auth']],

        // Panel Principal (Dashboard) - Admin y Supervisor
        '/dashboard' => ['AuthController', 'dashboard', ['auth', 'role:Administrador,Supervisor']],

        // Seguridad - Usuarios (Solo Administrador)
        '/seguridad/usuarios' => ['UsuarioController', 'index', ['auth', 'role:Administrador']],

        // Seguridad - Roles y Permisos (Solo Administrador)
        '/seguridad/roles' => ['RolController', 'index', ['auth', 'role:Administrador']],
        '/seguridad/permisos' => ['RolController', 'permisos', ['auth', 'role:Administrador']],

        // Operador
        '/operador/trabajos' => ['OperadorController', 'trabajos', ['auth', 'role:Operador']],
        '/operador/detalle'  => ['OperadorController', 'detalle', ['auth', 'role:Operador']],
    ],
    'POST' => [
        // Autenticación
        '/login' => ['AuthController', 'login', []],

        // Acciones de Usuarios (Solo Administrador)
        '/seguridad/usuarios/crear' => ['UsuarioController', 'store', ['auth', 'role:Administrador']],
        '/seguridad/usuarios/editar' => ['UsuarioController', 'update', ['auth', 'role:Administrador']],
        '/seguridad/usuarios/estado' => ['UsuarioController', 'toggleEstado', ['auth', 'role:Administrador']],

        // Acciones de Roles y Permisos (Solo Administrador)
        '/seguridad/roles/crear' => ['RolController', 'store', ['auth', 'role:Administrador']],
        '/seguridad/roles/editar' => ['RolController', 'update', ['auth', 'role:Administrador']],
        '/seguridad/roles/permisos' => ['RolController', 'guardarPermisos', ['auth', 'role:Administrador']],

        // Operador
        '/operador/concluir' => ['OperadorController', 'concluir', ['auth', 'role:Operador']],
    ],
];
