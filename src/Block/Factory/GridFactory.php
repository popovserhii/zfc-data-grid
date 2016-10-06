<?php
/**
 * Grid Factory
 *
 * @category Agere
 * @package Agere_Grid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 17.02.15 22:24
 */

namespace Agere\ZfcDataGrid\Block\Factory;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\InitializableInterface;

use Zend\ServiceManager\Exception;

use Agere\ZfcDataGridPlugin\Column\Factory\ColumnFactory;
use Agere\Block\Service\Plugin\BlockPluginManager;
use Agere\Block\Block\Admin\ActionPanel;
use Agere\Current\Plugin\Current;

class GridFactory implements AbstractFactoryInterface {

	public function canCreateServiceWithName(ServiceLocatorInterface $sm, $name, $requestedName) {
		return (substr($requestedName, -4) === 'Grid');
	}

	public function createServiceWithName(ServiceLocatorInterface $sm, $name, $requestedName) {
		$className = $this->getClassName($sm, $name, $requestedName);

		$translator = $sm->get('translator');
		//$config = $sm->get('Config');
		$om = $sm->get('Doctrine\ORM\EntityManager');
		$gpm = $sm->get('ControllerPluginManager');
		$vhm = $sm->get('ViewHelperManager');
		$renderer = $sm->get('ViewRenderer');
        /** @var BlockPluginManager $bpm */
		$bpm = $sm->get('BlockPluginManager');
		$urlPlugin = $gpm->get('url');
		/** @var Current $currentPlugin */
		$currentPlugin = $gpm->get('current');
		/** @var \Zend\Mvc\Router\RouteMatch $route */
        // Important get route from current plugin for correct work of forward
		//$route = $currentPlugin->currentRoute();
		// Important get route from current controller for correct work of forward
        $route = $currentPlugin->getController()->getEvent()->getRouteMatch();
		//$paramsPlugin = $currentPlugin->getController()->plugin('params');

		//$route = $sm->get('Application')->getMvcEvent()->getRouteMatch();
		$params = $route->getParams();

        //\Zend\Debug\Debug::dump($params); die(__METHOD__);

		#$url = [ // Important get route from 'params' plugin for correct work of forward
		#	'controller' => $paramsPlugin->fromRoute('controller'), 
		#	'action' => $paramsPlugin->fromRoute('action')
		#];

        //$routeMatch = $sm->get('Application')->getMvcEvent()->getRouteMatch();

        //echo $routeMatch->getMatchedRouteName();

		//\Zend\Debug\Debug::dump([$routeMatch->getMatchedRouteName()]); //die(__METHOD__);
		//\Zend\Debug\Debug::dump([$route->getMatchedRouteName()]); //die(__METHOD__);

        //\Zend\Debug\Debug::dump($urlPlugin->fromRoute($route->getMatchedRouteName(), $params)); //die(__METHOD__);

		$grid = clone $sm->get('ZfcDatagrid\Datagrid');
        $grid->setRendererName('jqGrid');
		$grid->setTranslator($translator);
		$grid->setToolbarTemplate('agere/grid/toolbar');
		$grid->setDefaultItemsPerPage(25);
		//$grid->setUrl($urlPlugin->fromRoute($route->getMatchedRouteName(), $url));
		$grid->setUrl($urlPlugin->fromRoute($route->getMatchedRouteName(), $params));

        $rendererOptions = $grid->getToolbarTemplateVariables();
        $rendererOptions['editUrl'] = [
            'route' => 'default/wildcard',
            'params' => [
                'controller' => 'data-grid',
                'action' => 'modify',
            ]
        ];
        //$rendererOptions['navGridDel'] = true;
        //$rendererOptions['navGridSearch'] = true;
        //$rendererOptions['inlineNavEdit'] = true;
        //$rendererOptions['inlineNavAdd'] = true;
        //$rendererOptions['inlineNavCancel'] = true;
        $grid->setToolbarTemplateVariables($rendererOptions);

        //\Zend\Debug\Debug::dump([$className, $rendererOptions['editUrl']]); die(__METHOD__);



        //$jqGridColumns = $vhm->get('jqgridColumns');

		$gridBlock = new $className($grid, $route, $renderer);

		/*if (isset($config['grid_block_config']['template_map']['grid/list'])
			&& $config['grid_block_config']['template_map']['grid/list']
		) {
			$grid->setTemplate($config['grid_block_config']['template_map']['grid/list']);
		}*/

        $gpm = $sm->get('DataGridPluginManager');

        //\Zend\Debug\Debug::dump([get_class($bpm), get_class($bpm->get('block/admin/toolbar'))]); die(__METHOD__);

		//$gridBlock->setToolbar($bpm->get('block/admin/toolbar'));
        $gridBlock->setToolbar($bpm->get('AdminToolbar'));

        $gridBlock->setColumnFactory(new ColumnFactory($gpm));
		if ($gridBlock instanceof ObjectManagerAwareInterface) {
			$gridBlock->setObjectManager($om);
		}

		if ($gridBlock instanceof InitializableInterface) {
			//$gridBlock->initToolbarCallback();
			$gridBlock->init();
		}

		return $gridBlock;
	}

	public function getClassName($sm, $name, $requestedName) {
		$aliases = $sm->get('Config')['service_manager']['aliases'];
		$fullName = isset($aliases[$requestedName]) ? $aliases[$requestedName] : '';

		if ((!$existsRequested = class_exists($requestedName)) && (!$existsFull = class_exists($fullName))) {
			throw new Exception\ServiceNotFoundException(sprintf(
				'%s: failed retrieving "%s%s"; class does not exist',
				get_class($this) . '::' . __FUNCTION__,
				$requestedName,
				($name ? '(alias: ' . $name . ')' : '')
			));
		}
		$className = $existsRequested ? $requestedName : $fullName;

		return $className;
	}

}