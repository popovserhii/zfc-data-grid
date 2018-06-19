<?php
/**
 * Grid Plugin Factory
 *
 * @category Popov
 * @package Popov_ZfcGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 26.11.2016 09:30
 */

namespace Popov\ZfcDataGrid\Controller\Plugin\Factory;

use Interop\Container\ContainerInterface;
use Popov\ZfcDataGrid\Controller\Plugin\GridPlugin;
use Popov\ZfcDataGrid\GridHelper;

class GridPluginFactory {

    public function __invoke(ContainerInterface $container)
    {
		$gridHelper = $container->get(GridHelper::class);

		return (new GridPlugin($gridHelper));
	}

}