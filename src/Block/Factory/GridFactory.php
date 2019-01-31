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
use Popov\ZfcDataGrid\Block\AbstractGrid;
use Popov\ZfcDataGrid\Service\UserSettingsService;
use Popov\ZfcDataGridPlugin\Service\Plugin\DataGridPluginFactory;
use Popov\ZfcDataGridPlugin\Service\Plugin\DataGridPluginManager;
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
use ZfcDatagrid\Datagrid;

class GridFactory implements AbstractFactoryInterface {

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $this->canCreateServiceWithName($container, $requestedName);
    }

	public function canCreateServiceWithName(ContainerInterface $container, $requestedName, array $options = null) {
		return (substr($requestedName, -4) === 'Grid');
	}

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $this->createServiceWithName($container, $requestedName, $options);
    }

	public function createServiceWithName(ContainerInterface$container, $requestedName, array $options = null) {
		$className = $this->getClassName($container, $requestedName);

        $translator = $container->get(TranslatorInterface::class);
		$config = $container->get('config');
		$om = $container->get(EntityManager::class);
		//$cpm = $container->get('ControllerPluginManager');
		//$vhm = $container->get(HelperPluginManager::class);
		//$renderer = $container->get('ViewRenderer');
        /** @var BlockPluginManager $bpm */
		$bpm = $container->get('BlockPluginManager');
        /** @var DataGridPluginManager $cpm */
        $cpm = $container->get('DataGridPluginManager');
        /** @var UrlHelper $urlHelper */
		$urlHelper = $container->get(UrlHelper::class);
		/** @var CurrentHelper $currentHelper */
		$currentHelper = $container->get(CurrentHelper::class);


		/** @var UserSettingsService $userSettingsService */
        $userSettingsService = $container->get(UserSettingsService::class);


        /** @var Datagrid $grid */
		$grid = clone $container->get('ZfcDatagrid\Datagrid');

        $grid->setRendererName('jqGrid');
        $grid->setTranslator($translator);
        $grid->setToolbarTemplate('grid/toolbar');
        $grid->setDefaultItemsPerPage(25);
        //$grid->setUrl($urlPlugin->fromRoute($route->getMatchedRouteName(), $url));
        $grid->setUrl($urlHelper->generate(
            $currentHelper->currentRouteName(),
            $currentHelper->currentRouteParams()
        ));

        if ($grid->getOptions()['settings']['export']['useTransSid']) {
            $grid->addParameter(...explode('=', SID));
        }

        /** @var AbstractGrid $gridBlock */
		$gridBlock = new $className();

		// We must create new ColumnFactory for each grid
        $gridBlock->setColumnFactory(new ColumnFactory($cpm, $config))
            ->setDataGrid($grid)
            ->setCurrentHelper($currentHelper)
            ->setUserSettingsService($userSettingsService)
            ->setToolbar($bpm->get('AdminToolbar'));

        if ($gridBlock instanceof ObjectManagerAwareInterface) {
            $gridBlock->setObjectManager($om);
        }

        /*if (isset($config['grid_block_config']['template_map']['grid/list'])
            && $config['grid_block_config']['template_map']['grid/list']
        ) {
            $grid->setTemplate($config['grid_block_config']['template_map']['grid/list']);
        }*/

        $gridBlock->initialize();

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