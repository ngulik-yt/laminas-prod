<?php
namespace Core;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

//api-tools = /api-tools/ui
return [
    'landing' => [
        'type'    => Literal::class,
        'options' => [
            'route'    => '/',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
                'is_caching' => true,
                'layout' => 'layout',
                'is_public' => true
            ],
        ],
    ]
];