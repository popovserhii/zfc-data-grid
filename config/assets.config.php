<?php
namespace Popov\ZfcDataGrid;

return [
    /*'default' => [
        'assets' => [
            '@jqGrid_css',
            '@jqGrid_js',
            //'@grid_css',
            '@grid_js',
        ],
    ],*/
    'routes' => [
        'admin(.*)' => [
            '@jqGrid_css',
            '@jqGrid_js',
            //'@grid_css',
            '@grid_js',
            '@formatters_js',
            '@grid_formatters_js',
        ],
    ],
    'modules' => [
        __NAMESPACE__ => [
            'root_path' => __DIR__ . '/../view/assets',
            'collections' => [
                'jqGrid_css' => [
                    'assets' => [
                        // js\jqGrid\css\ui.jqgrid.css
                        //'js/jqGrid/css/ui.jqgrid.css',
                        'js/jqGrid/lib/css/ui.jqgrid-bootstrap.css',
                    ],
                ],
                'jqGrid_js' => [
                    'assets' => [
                        // assets\js\jqGrid\js\jquery.jqGrid.min.js
                        'js/jqGrid/lib/js/jquery.jqGrid.min.js',
                        'js/jqGrid/lib/js/i18n/grid.locale-en.js',
                    ],
                ],
                'grid_js' => [
                    'assets' => [
                        'js/jqGrid/grid.js',
                        //'js/grid-remove.js',
                        'js/jqGrid/modal.js',
                    ],
                ],
                'formatters_js' => [
                    'assets' => [
                        'js/formatter/abstract-formatter.js',
                        'js/formatter/html-tag-formatter.js',
                        'js/formatter/link-formatter.js',
                    ],
                ],
                'grid_formatters_js' => [
                    'assets' => [
                        'js/jqGrid/formatter/grid.chain.js',
                        'js/jqGrid/formatter/grid.link.js',
                        'js/jqGrid/formatter/grid.drop-down.js',
                    ],
                ],
            ],
        ],
    ],
];