<?php
/**
 * Abstract class for Grid
 *
 * @category Popov
 * @package Popov_Grid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 11.01.2016 14:12
 */
namespace Popov\ZfcDataGrid\Block;

use Popov\ZfcCurrent\CurrentHelper;
use Zend\View\Renderer\PhpRenderer;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Column\Type;
use Popov\ZfcDataGridPlugin\Column\Factory\ColumnFactory;
use Popov\ZfcBlock\Block\Admin\Toolbar;

abstract class AbstractGrid
{
    /** @var Datagrid */
    protected $dataGrid;

    /** @var CurrentHelper */
    protected $currentHelper;

    /** @return PhpRenderer */
    protected $renderer;

    /** @var Toolbar */
    protected $toolbar;

    /** @var ColumnFactory */
    protected $columnFactory;

    protected $createButtonTitle = 'Create';

    protected $backButtonTitle = 'Back';

    /**
     * Grid id accord to entity mnemo
     *
     * @var string
     */
    protected $id = '';

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

    public function __construct(Datagrid $dataGrid, CurrentHelper $currentHelper)
    {
        $this->dataGrid = $dataGrid;
        $this->currentHelper = $currentHelper;
        $this->renderer = $currentHelper->currentRenderer();
        $dataGrid->setId($this->id);
        $this->initToolbarCallback();

        $this->actions = [
            'create' => [
                'admin/default' => [ // route name
                    'controller' => $currentHelper->currentController(), // route params
                    'action' => 'create',
                ],
            ],
            'back' => [
                'admin/default' => [ // route name
                    'controller' => $currentHelper->currentController(), // route params
                    'action' => 'back',
                ],
            ],
        ];
    }

    public function init()
    {}

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
			//$current = $this->getCurrentHelper();

            $toolbar->addButtonsWrapperClass('pull-right');
            // add button "Create" if relative label set
            !$this->getCreateButtonTitle() || $toolbar->addButton('create', [
                'label' => '+',
                'title' => $this->getCreateButtonTitle(),
                'href' => $this->actions['create'],
                'class' => 'btn btn-success btn-sm',
            ]);

            // add button "Back" if relative label set
            !$this->getBackButtonTitle() || $toolbar->addButton('back', [
                'label' => $this->getBackButtonTitle(),
                'title' => $this->getBackButtonTitle(),
                'href' => $this->actions['back'],
                'class' => 'btn btn-default btn-sm',
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
        $view = $this->getRenderer();

        foreach ($this->actions as $actionName => $action) {
            //$action = preg_replace('/([a-z]+)+([A-Z])/', '$1-$2', $grid->getId());
            //$action = strtolower($action);

            $link = $view->url($action['route'], $action['params']);
            $identity = isset($action['options']['identity']) ? $action['options']['identity'] : $grid->getId() . '_id';

            $bg = new Style\BackgroundColor([224, 226, 229]);
            $fmtr = new Column\Formatter\ExternalLink();
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

    public function setDataGrid($dataGrid)
    {
        $this->dataGrid = $dataGrid;

        return $this;
    }

    /**
     * @return Datagrid
     */
    public function getDataGrid()
    {
        return $this->dataGrid;
    }

    /**
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    public function getResponse()
    {
        return $this->getDataGrid()->getResponse();
    }

    public function setCurrentHelper(CurrentHelper $currentHelper)
    {
        $this->currentHelper = $currentHelper;

        return $this;
    }

    public function getCurrentHelper()
    {
        return $this->currentHelper;
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