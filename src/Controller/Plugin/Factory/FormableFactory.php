<?php
/**
 * Statusable plugin factory
 *
 * @category Popov
 * @package Popov_Status
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 04.02.15 10:30
 */

namespace Popov\ZfcDataGrid\Controller\Plugin\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\View\HelperPluginManager;

use Popov\ZfcDataGrid\Controller\Plugin\Formable;

class FormableFactory {

	public function __invoke(ServiceLocatorInterface $cpm) {
		$sm = $cpm->getServiceLocator();

		//$om = $sm->get('Doctrine\ORM\EntityManager');
		//$cm = $sm->get('ControllerPluginManager');
		$vhm = $sm->get('ViewHelperManager');

		$config = $sm->get('Config');
		$current = $cpm->get('current');
		$formElement = $vhm->get('formElement');

		$changer = $sm->get('StatusChanger');
		$moduleService = $sm->get('EntityService');

		return (new Formable($changer))
			->injectConfig($config)
			->injectCurrent($current)
			->injectModuleService($moduleService)
			->injectFormElementHelper($formElement)
		;
	}

}