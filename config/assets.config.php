<?php
namespace Popov\ZfcDataGrid;

return [
    //'assetic_configuration' => [
    'default' => [
        'assets' => [
            '@jqGrid_css',
            '@jqGrid_js',
            //'@grid_css',
            '@grid_js',
        ],
        /*'options' => [
            'mixin' => true,
        ],*/
    ],
    'modules' => [
        __NAMESPACE__ => [
            'root_path' => __DIR__ . '/../view/assets',
            'collections' => [
                'jqGrid_css' => [
                    'assets' => [
                        // js\jqGrid\css\ui.jqgrid.css
                        //'js/jqGrid/css/ui.jqgrid.css',
                        'js/jqGrid/css/ui.jqgrid-bootstrap.css',
                    ],
                ],
                'jqGrid_js' => [
                    'assets' => [
                        // assets\js\jqGrid\js\jquery.jqGrid.min.js
                        'js/jqGrid/js/jquery.jqGrid.min.js',
                        'js/jqGrid/js/i18n/grid.locale-en.js',
                    ],
                ],
                'grid_js' => [
                    'assets' => [
                        'js/grid.js',
                        //'js/grid-remove.js',
                        'js/modal.js',
                    ],
                ],
            ],
        ],
    ],
];