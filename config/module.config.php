<?php

namespace Popov\ZfcDataGrid;

use Popov\ZfcDataGrid\Service\UserSettingsService;

return [
    'assetic_configuration' => require_once 'assets.config.php',

    'progress' => [
        __NAMESPACE__ => [
            'context' => Service\Progress\DataGridContext::class,
        ]
    ],

    'ZfcDatagrid' => [
        'settings' => [
            'export' => [
                // Use SID in URL
                'useTransSid' => false,
            ],
        ],
    ],

    'dependencies' => [
        //'aliases' => [],
        //'invokables' => [],
        'factories' => [
            Service\Progress\DataGridContext::class => Service\Progress\Factory\DataGridContextFactory::class,
            Action\Admin\ModifyAction::class => Action\Admin\Factory\ModifyActionFactory::class,
        ],
        'delegators' => [
            Service\Progress\DataGridContext::class => [
                \Stagem\ZfcLang\Service\Factory\TranslatorDelegatorFactory::class
            ]
        ],
        'abstract_factories' => [
            Block\Factory\GridFactory::class,
        ],
    ],

    // middleware
    'actions' => [
        'data-grid' => __NAMESPACE__ . '\Action'
    ],
    
    // mvc
    'controllers' => [
        'aliases' => [
            'data-grid' => Controller\DataGridController::class,
        ],
        'factories' => [
            Controller\DataGridController::class => Controller\Factory\DataGridControllerFactory::class,
        ],
    ],

    // mvc
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

    // middleware
    'templates' => [
        'map' => [
            'grid/toolbar' => __DIR__ . '/../view/grid/toolbar.phtml',
            'zfc-datagrid/renderer/jqGrid/layout' => __DIR__ . '/../view/grid/layout.phtml',
            'zfc-datagrid/renderer/jqGrid/footer' => __DIR__ . '/../view/grid/footer.phtml',
            'zfc-datagrid/toolbar/export' => __DIR__ . '/../view/grid/export.phtml',
        ],
    ],

    // mvc
    'view_manager' => [
        'template_map' => [
            'grid/toolbar' => __DIR__ . '/../view/grid/toolbar.phtml',
            'zfc-datagrid/renderer/jqGrid/layout' => __DIR__ . '/../view/grid/layout.phtml',
            'zfc-datagrid/renderer/jqGrid/footer' => __DIR__ . '/../view/grid/footer.phtml',
            'zfc-datagrid/renderer/jqGrid/buttons' => __DIR__ . '/../view/grid/buttons.phtml',
            'zfc-datagrid/renderer/jqGrid/summarizer' => __DIR__ . '/../view/grid/summarizer.phtml',
            'zfc-datagrid/toolbar/export' => __DIR__ . '/../view/grid/export.phtml',
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

    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Model']
            ],
            'orm_default' => [
                'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                'drivers' => [
                    __NAMESPACE__ . '\Model' => __NAMESPACE__ . '_driver'
                ]
            ]
        ],
    ],
];
