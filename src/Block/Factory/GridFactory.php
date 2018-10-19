<?php
/**
 * Grid Factory
 *
 * @category Popov
 * @package Popov_Grid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 17.02.15 22:24
 */

namespace Popov\ZfcDataGrid\Block\Factory;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Popov\ZfcCore\Helper\UrlHelper;
use Popov\ZfcDataGrid\Service\UserSettingsService;
use Popov\ZfcUser\Form\User;
use Popov\ZfcUser\Helper\UserHelper;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\Stdlib\InitializableInterface;
use Zend\ServiceManager\Exception;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\HelperPluginManager;

use Popov\ZfcDataGridPlugin\Column\Factory\ColumnFactory;
use Popov\ZfcBlock\Plugin\BlockPluginManager;
//use Popov\ZfcBlock\Block\Admin\ActionPanel;
use Popov\ZfcCurrent\CurrentHelper;
use Popov\Simpler\SimplerHelper;

class GridFactory implements AbstractFactoryInterface {

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->canCreateServiceWithName($container, $requestedName);
    }

	public function canCreateServiceWithName($container, $requestedName, array $options = null) {
		return (substr($requestedName, -4) === 'Grid');
	}

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createServiceWithName($container, $requestedName, $options);
    }

	public function createServiceWithName($container, $requestedName, array $options = null) {
		$className = $this->getClassName($container, $requestedName);

        $translator = $container->get(TranslatorInterface::class);
		$config = $container->get('config');
		$om = $container->get(EntityManager::class);
		//$cpm = $container->get('ControllerPluginManager');
		//$vhm = $container->get(HelperPluginManager::class);
		//$renderer = $container->get('ViewRenderer');
        /** @var BlockPluginManager $bpm */
		$bpm = $container->get('BlockPluginManager');
		/** @var UrlHelper $urlHelper */
		$urlHelper = $container->get(UrlHelper::class);
		/** @var CurrentHelper $currentHelper */
		$currentHelper = $container->get(CurrentHelper::class);

		/** @var UserSettingsService $userSetting */
        $userSetting = $container->get(UserSettingsService::class);


        //$simplerHelper = $container->get(SimplerHelper::class);
        // Important get route from current plugin for correct work of forward
		//$route = $currentPlugin->currentRoute();
		// Important get route from current controller for correct work of forward
        //$route = $currentHelper->currentRoute();
		//$paramsPlugin = $currentPlugin->getController()->plugin('params');

		//$route = $sm->get('Application')->getMvcEvent()->getRouteMatch();
		//$params = $currentHelper->currentMatchedRouteParams();

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

		$grid = clone $container->get('ZfcDatagrid\Datagrid');
		$gridBlock = new $className($grid, $currentHelper, $userSetting);

        $grid->setRendererName('jqGrid');
        $grid->setTranslator($translator);
        $grid->setToolbarTemplate('grid/toolbar');
        $grid->setDefaultItemsPerPage(25);
        //$grid->setUrl($urlPlugin->fromRoute($route->getMatchedRouteName(), $url));
        $grid->setUrl($urlHelper->generate(
            $currentHelper->currentRouteName(),
            $currentHelper->currentRouteParams()
        ));

        $rendererOptions = $grid->getToolbarTemplateVariables();
        $rendererOptions['editUrl'] = [
            'route' => 'admin/default/wildcard',
            //'route' => 'admin/default',
            'params' => [
                'controller' => 'data-grid',
                'action' => 'modify',
                'grid' => $grid->getId(),
            ]
        ];
        //$rendererOptions['navGridDel'] = true;
        //$rendererOptions['navGridSearch'] = true;
        //$rendererOptions['inlineNavEdit'] = true;
        //$rendererOptions['inlineNavAdd'] = true;
        //$rendererOptions['inlineNavCancel'] = true;
        $grid->setToolbarTemplateVariables($rendererOptions);

        //\Zend\Debug\Debug::dump([$className, $rendererOptions['editUrl']]); die(__METHOD__);


        /*if (isset($config['grid_block_config']['template_map']['grid/list'])
            && $config['grid_block_config']['template_map']['grid/list']
        ) {
            $grid->setTemplate($config['grid_block_config']['template_map']['grid/list']);
        }*/

        $cpm = $container->get('DataGridPluginManager');

        //\Zend\Debug\Debug::dump([get_class($bpm), get_class($bpm->get('block/admin/toolbar'))]); die(__METHOD__);

		//$gridBlock->setToolbar($bpm->get('block/admin/toolbar'));
        $gridBlock->setToolbar($bpm->get('AdminToolbar'));

        // We must create new ColumnFactory for each grid
        $gridBlock->setColumnFactory(new ColumnFactory($cpm, /*$simplerHelper,*/ $config));
		if ($gridBlock instanceof ObjectManagerAwareInterface) {
			$gridBlock->setObjectManager($om);
		}

		if ($gridBlock instanceof InitializableInterface) {
			//$gridBlock->initToolbarCallback();
			$gridBlock->init();
		}

		return $gridBlock;
	}

	public function getClassName($container, $requestedName) {
		//$aliases = $container->get('config')['service_manager']['aliases'];
		//$fullName = isset($aliases[$requestedName]) ? $aliases[$requestedName] : '';

		//if ((!$existsRequested = class_exists($requestedName)) && (!$existsFull = class_exists($fullName))) {
		if ((!$container->has($requestedName))) {
			throw new Exception\ServiceNotFoundException(sprintf(
				'%s: failed retrieving "%s"; class does not exist'
                , get_class($this) . '::' . __FUNCTION__
                , $requestedName
                //, ($name ? '(alias: ' . $name . ')' : '')
			));
		}
		$className = $requestedName;

		return $className;
	}

}