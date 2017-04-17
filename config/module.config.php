<?php
namespace Agere\ZfcDataGrid;

return [
    'assetic_configuration' => require_once 'assets.config.php',

    'progress' => [
        __NAMESPACE__ => [
            'context' => Service\Progress\DataGridContext::class,
        ]
    ],

    'controllers' => [
        'aliases' => [
            'data-grid' => Controller\DataGridController::class,
        ],
        'factories' => [
            Controller\DataGridController::class => Controller\Factory\DataGridControllerFactory::class,
        ],
    ],

    'controller_plugins' => [
        'aliases' => [
            'grid' => Controller\Plugin\GridPlugin::class,
            'formable' => Controller\Plugin\Formable::class,
        ],
        'factories' => [
            Controller\Plugin\GridPlugin::class => Controller\Plugin\Factory\GridPluginFactory::class,
            Controller\Plugin\Formable::class => Controller\Plugin\Factory\FormableFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'agere/grid/toolbar' => __DIR__ . '/../view/agere/grid/toolbar.phtml',
            'zfc-datagrid/renderer/jqGrid/layout' => __DIR__ . '/../view/agere/grid/layout.phtml',
            'zfc-datagrid/renderer/jqGrid/footer' => __DIR__ . '/../view/agere/grid/footer.phtml',
            'zfc-datagrid/toolbar/export' => __DIR__ . '/../view/agere/grid/export.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'jqgridColumns' => View\Helper\Columns::class, // overwrite default jqGrid helper
        ],
    ],

    'service_manager' => [
        'factories' => [
            Service\Progress\DataGridContext::class => Service\Progress\Factory\DataGridContextFactory::class,
            Block\AgereDatagrid::class => \Agere\ZfcDataGrid\Block\Factory\DatagridFactory::class,
        ],
        'delegators' => [
            Service\Progress\DataGridContext::class => [
                \Agere\Translator\Service\Factory\TranslatorDelegatorFactory::class
            ]
        ],
        'abstract_factories' => [
            Block\Factory\GridFactory::class,
        ],
    ],

    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
                'text_domain' => __NAMESPACE__,
            ],
        ],
    ],

];
