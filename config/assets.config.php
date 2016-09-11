<?php
namespace Agere\ZfcDataGrid;

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
                            'js/jqGrid/css/ui.jqgrid-bootstrap.css'
                        ],
                    ],
                    'jqGrid_js' => [
                        'assets' => [
                            // assets\js\jqGrid\js\jquery.jqGrid.min.js
                            'js/jqGrid/js/jquery.jqGrid.min.js',
                            'js/jqGrid/js/i18n/grid.locale-ru.js',
                        ],
                    ],

                    'grid_js' => [
                        'assets' => [
                            'js/grid.js',
                            'js/grid-remove.js',
                            'js/modal.js',
                        ],
                    ],
                ],
            ],
        ],



   /* 'jqGrid' => [
        // <link rel="stylesheet" href="/media/js/jquery/jquery-ui-1.10.1.custom/css/custom-theme/jquery-ui-1.10.1.custom.min.css">
        // <link rel="stylesheet" href="/media/js/jquery/jqGrid/css/ui.jqgrid.css">

        //<script src="/media/js/jquery/jqGrid/js/i18n/grid.locale-ru.js"></script>
        //<script src="/media/js/jquery/jqGrid/jquery.jqGrid-4.6.0.js"></script>
        // vendor\twbs\bootstrap-sass\assets\stylesheets\_bootstrap.scss
        'root_path' => realpath('public/media/js/jquery/jqGrid'),
        'collections' => [
            'jqGrid_css' => [
                'assets' => [
                    'css/ui.jqgrid.css'
                ],
            ],
            'jqGrid_js' => [
                'assets' => [
                    // vendor\twbs\bootstrap-sass\assets\javascripts\bootstrap.min.js
                    'js/i18n/grid.locale-ru.js',
                    'jquery.jqGrid-4.6.0.js',
                ],
            ],
        ],
    ],*/
    //],
];