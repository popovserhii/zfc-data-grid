<?php
/**
 * Plugin which add form element to grid.
 *
 * This allow add status buttons to different fieldsets in form
 *
 * @category Popov
 * @package Popov_Grid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 26.03.16 19:37
 */

namespace Popov\ZfcDataGrid\Controller\Plugin;

use Zend\Stdlib\Exception;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Form\Form;
use Zend\Form\Fieldset;

use Magere\ZfcEntity\Service\EntityService as ModuleService;
use Magere\Status\Service\StatusChanger;
use Magere\Status\Form\ButtonFieldset;

use Popov\Current\Plugin\Current;

class Formable extends AbstractPlugin {

	/** @var Url */
	protected $config;

	/** @var Current */
	protected $current;

	/** @var StatusChanger */
	protected $statusChanger;

	/** @var ModuleService */
	protected $moduleService;

	protected $formElementHelper;

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

	public function __construct(StatusChanger $statusChanger) {
		$this->statusChanger = $statusChanger;
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

	protected function getSm() {
		return $this->getController()->getServiceLocator();
	}


	public function apply($form, $grid) {
		$this->qualifiedNames = [];
		$this->form = $form;
		$this->grid = $grid;
		//$this->qualifiedNames[] = $form->getName();

		//$quantity = current($form->get('invoice')->get('invoiceProducts'))->get('quantity')->getAttributes();
		//\Zend\Debug\Debug::dump(current($form->get('invoice')->get('invoiceProducts'))->get('quantity')->getAttributes()); die(__METHOD__);

		$validationGroup = $form->getValidationGroup();


		if ($validationGroup) {
            if ($grid instanceof \Popov\Invoice\Block\Grid\InvoiceProductGrid) { // @todo Видалити хардкод
                $this->prepareColumnsInvoiceProductGrid($validationGroup, $form);
            } else {
                $this->prepareColumns($validationGroup, $form);
            }
		}

		//\Zend\Debug\Debug::dump(get_class()); die(__METHOD__);
	}

	protected function prepareColumns($name, $form) {
		if (is_array($name)) {
			$i = 0;
			foreach ($name as $key => $value) {
				if (is_array($value)) {
					if ($form instanceof \Zend\Form\Element\Collection) {
						//$this->qualifiedNames[] = "[]";
						$form = $form->getTargetElement();

						//$this->qualifiedNames[] = "[' + iterator.count + ']";
						//$this->qualifiedNames[] = "[]";
						//$uniqueId = $form->getName() . '_id';
						//$this->qualifiedNames[] = "[' + rowObject.{$uniqueId} + ']";
						//$this->qualifiedNames[] = '[' . $i . ']';
						//\Zend\Debug\Debug::dump($uniqueId); die(__METHOD__);
					}

					//$this->qualifiedNames[] = count($this->qualifiedNames) ? '[' . $key . ']' : $key;

					//\Zend\Debug\Debug::dump(get_class($form));
					$this->prepareColumns($value, $form->get($key));

					//array_pop($this->qualifiedNames);

				} else {
					//\Zend\Debug\Debug::dump([$value, __METHOD__]);
					$this->prepareColumn($value, $form);
				}
				$i++;
			}
		} else {
			$this->prepareColumn($name, $form);
		}
	}

	protected function prepareColumn($name, $form) {
		//$formName = $form->getName();
		if ($form instanceof \Zend\Form\Element\Collection) {
			$form = $form->getTargetElement();
		}

		$element = $form->get($name);

		$formElement = $this->getFormElementHelper();

		$grid = $this->grid->getDataGrid();

		$columnName = $form->getName();
		//$columnName = substr($formName, 0, (strlen($formName) - 1));
		//$columnName = substr($formName, 0, (strlen($form->getName())));

		//\Zend\Debug\Debug::dump($columnName); die(__METHOD__);


		//\Zend\Debug\Debug::dump($form->getName()); die(__METHOD__);

		//\Zend\Debug\Debug::dump($name);
		//\Zend\Debug\Debug::dump(($element->getName()));
		//\Zend\Debug\Debug::dump(get_class($element)); die(__METHOD__);


		$uniqueId = $columnName . '_' . $element->getName();
		$relatedUniqueId = isset($this->related[$uniqueId]) ? $this->related[$uniqueId] : false;

		//\Zend\Debug\Debug::dump($uniqueId);

		$column = ($column = $grid->getColumnByUniqueId($uniqueId))
			? $column
			: $grid->getColumnByUniqueId($relatedUniqueId);

		if (!$column) {
			// try get dynamic columns
			$item = $this->getStatusChanger()->setItem($this->form->getObject())->getItemWithStatus();
			//\Zend\Debug\Debug::dump(get_class($item));die(__METHOD__);

			//if (method_exists($item, 'getStatus')) {
				$statusMnemo = $item->getStatus()->getMnemo();
				//$uniqueId = $element->getName() . '_' . $statusMnemo;

				$column = $grid->getColumnByUniqueId($columnName . '_' . $statusMnemo);
				//\Zend\Debug\Debug::dump(get_class($column)); //die(__METHOD__);
			//}
		}

		if (!$column) {
			# throw new Exception\RuntimeException(sprintf('Unresolved column Unique Id %s. '
			#	. 'Maybe you need add key=>value pare to related property', $uniqueId));
            return false;
		}

        // $this->prepareColumnsInvoiceProductGrid($validationGroup, $form);
        // 'renderer_parameter' => ['editable', true, 'jqGrid'],
        //$rendererParameter = $column->getRendererParameters();
        $editable = true;
        if ($element->getAttribute('disabled') || $element->getAttribute('readonly')) {
            $editable = false;
        }
        $column->setRendererParameter('editable', $editable, 'jqGrid');

        //$column->setRendererParameter('formatter', $formatterJs, $grid->getRendererName());

		//\Zend\Debug\Debug::dump($html); die(__METHOD__);

		array_pop($this->qualifiedNames);
	}










	protected function prepareColumnsInvoiceProductGrid($name, $form) {
		if (is_array($name)) {
			$i = 0;
			foreach ($name as $key => $value) {
				if (is_array($value)) {
					if ($form instanceof \Zend\Form\Element\Collection) {
						//$this->qualifiedNames[] = "[]";
						$form = $form->getTargetElement();

						$this->qualifiedNames[] = "[' + iterator.count + ']";
						//$this->qualifiedNames[] = "[]";
						//$uniqueId = $form->getName() . '_id';
						//$this->qualifiedNames[] = "[' + rowObject.{$uniqueId} + ']";
						//$this->qualifiedNames[] = '[' . $i . ']';
						//\Zend\Debug\Debug::dump($uniqueId); die(__METHOD__);
					}

					$this->qualifiedNames[] = count($this->qualifiedNames) ? '[' . $key . ']' : $key;

					//\Zend\Debug\Debug::dump(get_class($form));
					$this->prepareColumnsInvoiceProductGrid($value, $form->get($key));

					array_pop($this->qualifiedNames);

				} else {
					//\Zend\Debug\Debug::dump([$value, __METHOD__]);
					$this->prepareColumnInvoiceProductGrid($value, $form);
				}
				$i++;
			}
		} else {
			$this->prepareColumnInvoiceProductGrid($name, $form);
		}
	}

	protected function prepareColumnInvoiceProductGrid($name, $form) {
		//$formName = $form->getName();
		if ($form instanceof \Zend\Form\Element\Collection) {
			$form = $form->getTargetElement();
		}

		$element = $form->get($name);

		$formElement = $this->getFormElementHelper();

		$grid = $this->grid->getDataGrid();

		$columnName = $form->getName();
		//$columnName = substr($formName, 0, (strlen($formName) - 1));
		//$columnName = substr($formName, 0, (strlen($form->getName())));

		//\Zend\Debug\Debug::dump($columnName); die(__METHOD__);


		//\Zend\Debug\Debug::dump($form->getName()); die(__METHOD__);

		//\Zend\Debug\Debug::dump($name);
		//\Zend\Debug\Debug::dump(($element->getName()));
		//\Zend\Debug\Debug::dump(get_class($element)); die(__METHOD__);


		$uniqueId = $columnName . '_' . $element->getName();
		$relatedUniqueId = isset($this->related[$uniqueId]) ? $this->related[$uniqueId] : false;

		//\Zend\Debug\Debug::dump($uniqueId);

		$column = ($column = $grid->getColumnByUniqueId($uniqueId))
			? $column
			: $grid->getColumnByUniqueId($relatedUniqueId);

		if (!$column) {
			// try get dynamic columns
			$item = $this->getStatusChanger()->setItem($this->form->getObject())->getItemWithStatus();
			//\Zend\Debug\Debug::dump(get_class($item));die(__METHOD__);

			//if (method_exists($item, 'getStatus')) {
				$statusMnemo = $item->getStatus()->getMnemo();
				//$uniqueId = $element->getName() . '_' . $statusMnemo;

				$column = $grid->getColumnByUniqueId($columnName . '_' . $statusMnemo);
				//\Zend\Debug\Debug::dump(get_class($column)); //die(__METHOD__);
			//}
		}

		if (!$column) {
			# throw new Exception\RuntimeException(sprintf('Unresolved column Unique Id %s. '
			#	. 'Maybe you need add key=>value pare to related property', $uniqueId));
            return false;
		}

		#$rowId = $columnName . '_id';
		//$this->qualifiedNames[] = "[' + iterator.count + ']";
		$this->qualifiedNames[] = "[0]";
		//$this->qualifiedNames[] = "[' + rowObject.{$rowId} + ']";

		$inputName = implode('', $this->qualifiedNames);


		#$hiddenHtml = str_replace( // required element
		#	[sprintf('name="%s"', 'id'), 'value=""'],
		#	[sprintf('name="%s"', $inputName . "[id]"), sprintf('value="\' + rowObject.%s + \'"', $rowId)],
		#	'<input type="hidden" name="id" value="">'
		#);

		$inputHtml = str_replace(
			[sprintf('name="%s"', $element->getName()), 'value=""'],
			[sprintf('name="%s"', $inputName . "[{$name}]"), sprintf('value="\' + rowObject.%s + \'"', $column->getUniqueId())],
			$formElement($element)
		);

		//\Zend\Debug\Debug::dump([get_class($element), $element->getName(), $element->getAttribute('disabled')]); die(__METHOD__);


		$formatterJs = <<<FORMATTER_JS
function (value, options, rowObject) {
	var iterator = $.data(document.body, '__iterator');
	if (!iterator) {
		iterator = { count: 0, rowId: options.rowId };
		$.data(document.body, '__iterator', iterator);
	} else if (iterator.rowId != options.rowId) {
        iterator.rowId = options.rowId;
        iterator.count++;
	}

	//return '{\$hiddenHtml}' + '{$inputHtml}';
	return '{$inputHtml}';
}
FORMATTER_JS;

		/*$formatterJs = <<<FORMATTER_JS
function (value, options, rowObject) {
	var iterator = $.data(document.body, '__iterator');
	if (!iterator) {
		iterator = { count: -1 };
		$.data(document.body, '__iterator', iterator);
	}
	iterator.count++;

	//console.log(rowObject);

	return '<input type="hidden" name="{$inputName}[id]" value="' + rowObject.{$rowId} + '">'
		+ '{$inputHtml}'
	;
}
FORMATTER_JS;*/


		//$formatterRow = $column->getRendererParameters($grid->getRendererName())['formatter'];
		//$formatter = sprintf($formatterRow, $html);

		$column->setRendererParameter('formatter', $formatterJs, $grid->getRendererName());

		//\Zend\Debug\Debug::dump($html); die(__METHOD__);

		array_pop($this->qualifiedNames);
	}


	public function __invoke() {
		if (!$args = func_get_args()) {
			return $this;
		}

		return call_user_func([$this, 'apply'], func_get_args());
	}

}