<?php
/**
 * Plugin which add form element to grid.
 *
 * This allow add status buttons to different fieldsets in form
 *
 * @category Agere
 * @package Agere_Grid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 26.03.16 19:37
 */

namespace Agere\ZfcDataGrid\Controller\Plugin;

use Zend\Filter\Word\CamelCaseToDash;
use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;

use Magere\Entity\Service\EntityService as ModuleService;
use Magere\Status\Service\StatusChanger;
use Magere\Status\Form\ButtonFieldset;

use Agere\Current\Plugin\Current;

class GridPlugin extends AbstractPlugin {

	/** @var Url */
	protected $config;

	/** @var Current */
	protected $current;

	/** @var StatusChanger */
	protected $statusChanger;

	/** @var ModuleService */
	protected $moduleService;

	protected $formElementHelper;

    protected $route;

	/**
	 * Temporary fields data of which should display on form
	 * and then save to other object with related name
	 *
	 * Explain:
	 * 	key - form field to which will be bind value
	 *	value - intermediate field which will be transfer to 'key'
	 *
	 * @var array
	 */
	protected $related = [
		//'productCity_quantity' => 'invoiceProduct_quantity'
	];

	public function __construct(/*StatusChanger $statusChanger*/) {
		/*$this->statusChanger = $statusChanger;*/
	}

	public function injectConfig($config) {
		$this->config = $config;

		return $this;
	}

	public function getConfig() {
		return $this->config;
	}

	public function injectCurrent($current) {
		$this->current = $current;

		return $this;
	}

	public function getCurrent() {
		return $this->current;
	}

	public function getStatusChanger() {
		return $this->statusChanger;
	}

	public function injectModuleService($moduleService) {
		$this->moduleService = $moduleService;

		return $this;
	}

	public function getModuleService() {
		return $this->moduleService;
	}

	public function injectFormElementHelper($formElementHelper) {
		$this->formElementHelper = $formElementHelper;

		return $this;
	}

	public function getFormElementHelper() {
		return $this->formElementHelper;
	}

	public function getRoute()
    {
        if (!$this->route) {
            $this->route = $this->getController()->getEvent()->getRouteMatch();
        }

        return $this->route;
    }

	public function prepareExchangeData($request)
    {
        $route = $this->getRoute();
        $gridData = [];
        foreach ($request->getPost() as $name => $value) {
            if (in_array($name, ['id', 'oper']) || (substr($name, -3, 3) === '_id')) { // skip specialized keywords
                continue;
            }

            $filter = new CamelCaseToDash();
            list($moduleMnemo, $field) = explode('_', $name);
            $moduleMnemoAlias = strtolower($filter->filter($moduleMnemo));
            $gridMnemo = $route->getParam('grid');
            if ($itemId = $request->getPost($moduleMnemo . '_id')) {
                $gridData[$moduleMnemoAlias][$itemId][$field] = $value;
            } elseif ($gridMnemo === $moduleMnemo) {
                $itemId = $request->getPost('id');
                $gridData[$moduleMnemoAlias][$itemId][$field] = $value;
            }
        }

        return $gridData;
    }

	/*public function __invoke() {
		if (!$args = func_get_args()) {
			return $this;
		}

		return call_user_func([$this, 'apply'], func_get_args());
	}*/

}