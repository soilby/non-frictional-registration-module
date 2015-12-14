<?php

return array(
    'service_manager' => [
        'alias' => [],
        'invokables' => [],
        'factories' => []
    ],
    'controllers' => [
        'invokables' => []
    ],
    'view_helpers' => [
        'invokables' => [],
        'factories' => [
            'soilOnSiteNotificationWidget' => 'Soil\OnSiteNotificationClient\View\Helper\NotificationEngineFactory'
        ]
    ]

);