<?php
/**
 * Configuración de APIs Externas
 * 
 * URLs base de las APIs que el sistema consume para obtener
 * datos de reconexiones y reclamos (ver AGENTS.md §11).
 */
return [
    'reconexiones' => [
        'base_url' => getenv('API_RECONEXIONES_URL') ?: '',
    ],
    'reclamos' => [
        'base_url' => getenv('API_RECLAMOS_URL') ?: '',
    ],
    'fotos' => [
        'base_url' => getenv('API_FOTOS_URL') ?: 'https://chatbot.cosmol.com.bo',
    ],
];
