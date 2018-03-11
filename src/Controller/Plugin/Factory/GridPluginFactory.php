<?php
/**
 * Grid Plugin Factory
 *
 * @category Popov
 * @package Popov_Grid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 26.11.2016 09:30
 */

namespace Popov\ZfcDataGrid\Controller\Plugin\Factory;

use Interop\Container\ContainerInterface;
use Popov\ZfcDataGrid\Controller\Plugin\GridPlugin;

class GridPluginFactory {

    public function __invoke(ContainerInterface $container)
    {
		$sm = $container->getServiceLocator();

		//$om = $sm->get('Doctrine\ORM\EntityManager');
		//$cm = $sm->get('ControllerPluginManager');
		$vhm = $sm->get('ViewHelperManager');

		$config = $sm->get('Config');
		$current = $container->get('current');
		$formElement = $vhm->get('formElement');

		$changer = $sm->get('StatusChanger');
		$moduleService = $sm->get('EntityService');

		return (new GridPlugin(/*$changer*/))
			//->injectConfig($config)
			//->injectCurrent($current)
			//->injectModuleService($moduleService)
			//->injectFormElementHelper($formElement)
		;
	}

}