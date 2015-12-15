<?php

return array(
    'service_manager' => [
        'alias' => [],
        'invokables' => [],
        'factories' => [
            'SoilNonFrictionalRegistrar' => 'Soil\NonFrictionalRegistration\Service\Factory\EmailRegistrarFactory',
            'SoilNonFrictionalCodeIssuerService' => 'Soil\NonFrictionalRegistration\Service\Factory\CodeIssuerServiceFactory'
        ]
    ],
    'controllers' => [
        'invokables' => []
    ],
    'view_helpers' => [
        'invokables' => [],
        'factories' => [
        ]
    ]

);