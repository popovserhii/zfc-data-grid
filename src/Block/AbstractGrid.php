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
use Popov\ZfcDataGrid\Model\UserSettings;
use Popov\ZfcDataGrid\Service\UserSettingsService;
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

    /** @var UserSettingsService */
    protected $userSettingsService;

    /** @return PhpRenderer */
    protected $renderer;

    /** @var Toolbar */
    protected $toolbar;

    /** @var ColumnFactory */
    protected $columnFactory;

    protected $createButtonTitle = 'Create';

    protected $backButtonTitle = 'Back';

    /**
     * Unique Grid ID (mnemo)
     *
     * @var string
     */
    protected $id = '';

    protected $actions = [];

    public function setId($id)
    {
        $this->getDataGrid()->setId($id);
    }

    public function getId()
    {
        return str_replace('_grid', '', $this->getDataGrid()->getId());
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
        return $this->currentHelper->currentRenderer();
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

    public function setUserSettingsService(UserSettingsService $settingsService)
    {
        $this->userSettingsService = $settingsService;

        return $this;
    }

    public function getUserSettingsService()
    {
        return $this->userSettingsService;
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
     * Default Grid preparation
     */
    final public function initialize()
    {
        $this->setId($this->id);

        $this->initDefault();
        $this->initToolbarCallback();
    }

    /**
     * Custom Grid preparation
     *
     * Here you add columns, buttons, etc.
     */
    public function init()
    {}

    public function add(array $columnConfig)
    {
        $column = $this->getColumnFactory()->create($columnConfig);

        // @todo Implement more elegant solution
        $this->userSettingsService->apply($column, $this->getDataGrid()->getId());
        $this->getDataGrid()->addColumn($column);

        return $this;
    }

    public function addButton(array $buttonConfig)
    {
        $button = $this->getColumnFactory()->createButton($buttonConfig);

        $rendererOptions = $this->getDataGrid()->getToolbarTemplateVariables();
        $rendererOptions['navButtons'][$button->getName()] = $button;

        $this->getDataGrid()->setToolbarTemplateVariables($rendererOptions);

        return $this;
    }

	public function initDefault() {

        $this->addButton(['name' => 'ColumnChooser']);

        $this->actions = [
            'create' => [
                'admin/default' => [ // route name
                    'controller' => $this->currentHelper->currentController(), // route params
                    'action' => 'create',
                ],
            ],
            'back' => [
                'admin/default' => [ // route name
                    'controller' => $this->currentHelper->currentController(), // route params
                    'action' => 'back',
                ],
            ],
        ];

        $grid = $this->getDataGrid();
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

        return $this;
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