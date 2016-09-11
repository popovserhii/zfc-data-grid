<?php
/**
 * Abstract class for Grid
 *
 * @category Agere
 * @package Agere_Grid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 11.01.2016 14:12
 */
namespace Agere\ZfcDataGrid\Block;

use Magere\Cart\Grid\Column\Action\AddToCart;
use Zend\Stdlib\InitializableInterface;
use Zend\View\Renderer\PhpRenderer;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Column\Type;

use Agere\ZfcDataGridPlugin\Column\Factory\ColumnFactory;
use Agere\ZfcDataGrid\View\Helper\Columns as ColumnsHelper;
use Agere\Block\Block\Admin\Toolbar;

abstract class AbstractGrid implements InitializableInterface
{
    /** @var Datagrid */
    protected $dataGrid;

    /** @var ColumnsHelper */
    protected $columnsHelper;

    /** @var Toolbar */
    protected $toolbar;

    /** @var ColumnFactory */
    protected $columnFactory;

    protected $createButtonTitle = 'Create';

    protected $backButtonTitle = 'Back';

    protected $actions = [
        /*'view' => [
            'route' => 'default',
            'params' => [
                'controller' => null,
                'action' => 'view',
            ],
            'options'
        ],
        'edit' => [
            'route' => 'default/id',
            'params' => [
                'controller' => null,
                'action' => 'edit',
            ],
        ],*/
    ];

    public function __construct(Datagrid $dataGrid, ColumnsHelper $columnsHelper)
    {
        $this->dataGrid = $dataGrid;
        $this->columnsHelper = $columnsHelper;
        $this->initToolbarCallback();
    }

    public function setCustomColumnModelOptions($col, array $options)
    {
        $this->getColumnsHelper()->setCustomColumnModelOption($col, $options);

        return $this;
    }

    public function getId()
    {
        //return explode('_', $this->getDataGrid()->getId())[0];
        return str_replace('_grid', '', $this->getDataGrid()->getId());
    }

    public function add(array $columnConfig)
    {
        $column = $this->getColumnFactory()->create($columnConfig);
        $this->getDataGrid()->addColumn($column);

        return $this;
    }

	public function initToolbarCallback() {
        $grid = $this->getDataGrid();
        if (isset($grid->getToolbarTemplateVariables()['toolbar'])) {
            return $this;
        }

		$toolbarCallback = function() {
            static $toolbar;

            if (null !== $toolbar) {
                return $toolbar;
            }

			$toolbar = $this->getToolbar();
			$route = $this->getRouteMatch();

            $toolbar->addButtonsWrapperClass('pull-right');
            // add button "Create" if relative label set
            !$this->getCreateButtonTitle()
            || $toolbar->addButton('create', [
                'label' => '+',
                'title' => $this->getCreateButtonTitle(),
                'href' => [
                    'default' => [ // route name
                        'controller' => $route->getParam('controller'), // route params
                        'action' => 'create',
                    ]
                ],
                'class' => 'btn btn-success btn-md',
            ]);
            // add button "Back" if relative label set
            !$this->getBackButtonTitle()
            || $toolbar->addButton('back', [
                'label' => $this->getBackButtonTitle(),
                'title' => $this->getBackButtonTitle(),
                'href' => [
                    'default' => [ // route name
                        'controller' => $route->getParam('controller'), // route params
                        'action' => 'back',
                    ]
                ],
                'class' => 'btn btn-default btn-md',
            ]);
            $this->initToolbar();
            //$actionBlock = $toolbar->createActionPanel();
            //$actionBlock = $this->block('block/admin/actionPanel');
            return $toolbar;
        };

        $rendererOptions = $grid->getToolbarTemplateVariables();
        $rendererOptions['toolbar'] = $toolbarCallback;
        $grid->setToolbarTemplateVariables($rendererOptions);
    }

    public function prepareActionColumn()
    {
        $grid = $this->getDataGrid();
        //$route = $this->getRouteMatch();
        $view = $this->getViewRenderer();

        foreach ($this->actions as $actionName => $action) {
            //$action = preg_replace('/([a-z]+)+([A-Z])/', '$1-$2', $grid->getId());
            //$action = strtolower($action);

            $link = $view->url($action['route'], $action['params']);
            $identity = isset($action['options']['identity']) ? $action['options']['identity'] : $grid->getId() . '_id';

            $bg = new Style\BackgroundColor([224, 226, 229]);
            $fmtr = new Column\Formatter\Link();
            $fmtr->setAttribute('class', $action['attributes']['class']);

            $fmtr->setLink($link . '/' . $fmtr->getColumnValuePlaceholder($grid->getColumnByUniqueId($identity)));

            $actions = new Column\Action($actionName);
            $actions->setLabel(' ');
            //$actions->addAction($viewAction);
            $actions->setTranslationEnabled();
            $actions->setFormatters([$fmtr]);
            $actions->addStyle($bg);
            $actions->setWidth(1);
            $grid->addColumn($actions);
        }

        //return $actions;
    }

    public function prepareActionColumnOld()
    {
        $grid = $this->getDataGrid();
        $route = $this->getRouteMatch();
        $view = $this->getViewRenderer();

        foreach ($this->actions as $action) {
            $action = preg_replace('/([a-z]+)+([A-Z])/', '$1-$2', $grid->getId());
            $action = strtolower($action);
            $action = $view->url($route->getMatchedRouteName(), [
                //'controller' => $grid->getId(),
                'controller' => $route->getParam('controller'),
                'action' => 'edit-' . $action,
            ]);


            $bg = new Style\BackgroundColor([224, 226, 229]);
            $fmtr = new Column\Formatter\Link();
            $fmtr->setAttribute('class', 'pencil-edit-icon');
            \Zend\Debug\Debug::dump($action
                . '/'
                . $fmtr->getColumnValuePlaceholder($grid->getColumnByUniqueId($grid->getId() . '_id')));
            die(__METHOD__);
            $fmtr->setLink($action . '/' . $fmtr->getColumnValuePlaceholder($grid->getColumnByUniqueId($grid->getId()
                    . '_id')));
            $actions = new Column\Action('edit');
            $actions->setLabel(' ');
            //$actions->addAction($viewAction);
            $actions->setTranslationEnabled();
            $actions->setFormatters([$fmtr]);
            $actions->addStyle($bg);
            $actions->setWidth(1);
            $grid->addColumn($actions);
        }

        return $actions;
    }

    /*public function prepareActionUrl($type)
    {
        $grid = $this->getDataGrid();
        $route = $this->getRouteMatch();
        $view = $this->getViewRenderer();

        $action = preg_replace('/([a-z]+)+([A-Z])/', '$1-$2', $grid->getId());
        $action = strtolower($action);
        $action = $view->url($route->getMatchedRouteName(), [
            //'controller' => $grid->getId(),
            'controller' => $route->getParam('controller'),
            'action' => 'edit-' . $action,
        ]);
    }*/

    public function getDataGrid()
    {
        return $this->dataGrid;
    }

    public function getColumnsHelper()
    {
        return $this->columnsHelper;
    }

    public function getResponse()
    {
        return $this->getDataGrid()->getResponse();
    }

    public function generateLink($route, $key = null, $params = [])
    {
        $sm = $this->getServiceLocator();

        return new Column\Formatter\GenerateLink($sm, $route, $key, $params);
    }

    public function getServiceLocator()
    {
        static $sm;
        if (!$sm) {
            $sm = $this->getColumnsHelper()->getServiceLocator()->getServiceLocator();
        }

        return $sm;
    }

    public function getRouteMatch()
    {
        static $routeMatch;
        if (!$routeMatch) {
            $routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        }

        return $routeMatch;
    }

    /**
     * @return PhpRenderer
     */
    public function getViewRenderer()
    {
        static $viewRenderer;
        if (!$viewRenderer) {
            $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');
        }

        return $viewRenderer;
    }

    public function setToolbar(Toolbar $toolbar)
    {
        $this->toolbar = $toolbar;
    }

    public function getToolbar()
    {
        return $this->toolbar;
    }

    /**
     * Overwrite this method for add custom elements
     *
     * @return Toolbar
     */
    public function initToolbar()
    {
        return $this->toolbar;
    }

    public function setColumnFactory(ColumnFactory $columnFactory)
    {
        $this->columnFactory = $columnFactory;
    }

    public function getColumnFactory()
    {
        return $this->columnFactory;
    }

	public function getCreateButtonTitle() {
		return $this->createButtonTitle;
	}

	public function getBackButtonTitle() {
		return $this->backButtonTitle;
	}

	public function getTranslator() {
		return $this->getDataGrid()->getTranslator();
	}

	/**
	 * Translate a message.
	 *
	 * @param $message
	 * @return string
	 */
	public function __($message) {
		return $this->getDataGrid()->getTranslator()->translate($message);
	}

	/**
	 * Translate a plural message.
	 *
	 * @param $singular
	 * @param $plural
	 * @param $number
	 * @return string
	 */
	public function ___($singular, $plural, $number) {
		return $this->getDataGrid()->getTranslator()->translatePlural($singular, $plural, $number);
	}

}