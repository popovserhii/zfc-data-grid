<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcDataGrid;

use Zend\Stdlib\ArrayUtils;

class ConfigProvider
{
    public function __invoke()
    {
        $originConfig = require __DIR__ . '/../../../zfc-datagrid/zfc-datagrid/config/module.config.php';
        $originConfig['dependencies'] = $originConfig['service_manager'];
        unset($originConfig['service_manager']);

        $customConfig = require __DIR__ . '/../config/module.config.php';

        $config = ArrayUtils::merge($originConfig, $customConfig);

        unset($config['controllers']);
        unset($config['controllers']);
        unset($config['controller_plugins']);
        unset($config['view_manager']);

        return $config;
    }
}