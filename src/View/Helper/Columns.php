<?php
/**
 * Overwrite default jqGrid helper
 *
 * @category Agere
 * @package Agere_Helper
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 04.01.2016 15:15
 */
namespace Agere\ZfcDataGrid\View\Helper;

use ZfcDatagrid\Renderer\JqGrid\View\Helper\Columns as ZfcDatagridColumns;

use ZfcDatagrid\Column\Type;

class Columns extends ZfcDatagridColumns {

	protected $columnModelOptions = [];

	/** All parent methods must be protected or public */

	public function __invoke(array $columns) {
		$return = [];

		foreach ($columns as $column) {
			/* @var $column \ZfcDatagrid\Column\AbstractColumn */

			$options = [
				'name'  => (string) $column->getUniqueId(),
				'index' => (string) $column->getUniqueId(),
				'label' => $this->translate((string) $column->getLabel()),

				'width'    => $column->getWidth(),
				'hidden'   => (bool) $column->isHidden(),
				'sortable' => (bool) $column->isUserSortEnabled(),
				'search'   => (bool) $column->isUserFilterEnabled(),
			];

			/*
			 * Formatting
			 */
			$formatter = $this->getFormatter($column);
			if ($formatter != '') {
				$options['formatter'] = (string) $formatter;
			}

			if ($column->getType() instanceof Type\Number) {
				$options['align'] = (string) 'right';
			}

			/*
			 * Cellattr
			 */
			$rendererParameters = $column->getRendererParameters('jqGrid');
			if (isset($rendererParameters['cellattr'])) {
				$options['cellattr'] = (string) $rendererParameters['cellattr'];
			}
			if (isset($rendererParameters['classes'])) {
				$options['classes'] = (string) $rendererParameters['classes'];
			}
            if (isset($rendererParameters['editable'])) {
                $options['editable'] = (bool) $rendererParameters['editable'];
            }


			/*
			 * Filtering
			 */
			$searchoptions                = [];
			$searchoptions['clearSearch'] = false;
			if ($column->hasFilterSelectOptions() === true) {
				$options['stype']       = 'select';
				$searchoptions['value'] = $column->getFilterSelectOptions();

				if ($column->hasFilterDefaultValue() === true) {
					$searchoptions['defaultValue'] = $column->getFilterDefaultValue();
				} else {
					$searchoptions['defaultValue'] = '';
				}
			} elseif ($column->hasFilterDefaultValue() === true) {
				$filter = new \ZfcDatagrid\Filter();
				$filter->setFromColumn($column, $column->getFilterDefaultValue());

				$searchoptions['defaultValue'] = $filter->getDisplayColumnValue();
			}

			if (count($searchoptions) > 0) {
				$options['searchoptions'] = $searchoptions;
			}

			if ($customOpt = $this->getCustomColumnModelOption($column)) {
				$options = array_merge($options, $customOpt);
				//\Zend\Debug\Debug::dump($options); die(__METHOD__);
			}

			$return[] = $this->buildColModel($options);
		}

		return '[' . implode(',', $return) . ']';
	}

	public function buildColModel($options) {
		/**
		 * Because with json_encode we get problems, it's custom made!
		 */
		$colModel = [];
		$specialOptions = ['formatter', 'searchoptions', 'editoptions'];
		foreach ($options as $key => $value) {
			if (is_array($value) && in_array($key, $specialOptions)) {
				$options = [];
				foreach ($value as $k => $v) {
					$options[] = (string) $k . ': ' . $this->buildValue($k, $v);
				}
				$value = '{' . implode(',', $options) . '}';
			} else {
				$value = $this->buildValue($key, $value);
			}

			$colModel[] = (string) $key . ': ' . $value;
		}

		$return = '{' . implode(',', $colModel) . '}';

		return $return;
	}

	public function buildValue($key, $value) {
		if (is_array($value)) {
			$value = json_encode($value);
		} elseif (is_bool($value)) {
			if (true === $value) {
				$value = 'true';
			} else {
				$value = 'false';
			}
		} elseif ('formatter' == $key) {
			if (stripos($value, 'formatter') === false && stripos($value, 'function') === false) {
				$value = '"' . $value . '"';
			}
		} elseif ('cellattr' == $key) {
			// SKIP THIS
		} elseif (stripos($value, 'function') !== false) {
			// SKIP THIS
		} else {
			$value = '"' . $value . '"';
		}

		return $value;
	}

	public function setCustomColumnModelOption($col, array $options) {
		$this->columnModelOptions[$col->getUniqueId()] = $options;
	}

	public function getCustomColumnModelOption($col) {
		if (isset($this->columnModelOptions[$col->getUniqueId()])) {
			return $this->columnModelOptions[$col->getUniqueId()];
		}

		return false;
	}

}