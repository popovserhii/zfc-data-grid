<?php
/**
 * Overwrite default jqGrid helper
 *
 * @category Popov
 * @package Popov_Helper
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 04.01.2016 15:15
 */
namespace Popov\ZfcDataGrid\View\Helper;

use ZfcDatagrid\Renderer\JqGrid\View\Helper\Columns as ZfcDatagridColumns;

use ZfcDatagrid\Filter;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Type;

class Columns extends ZfcDatagridColumns {

	protected $columnModelOptions = [];

	public function __invoke(array $columns) {
		$return = [];

        $class = new \ReflectionClass($this);
        $translateMethod = $class->getMethod('translate');
        $translateMethod->setAccessible(true);

        //$getFormatterMethod = $class->getMethod('getFormatter');
        //$getFormatterMethod->setAccessible(true);

		foreach ($columns as $column) {
			/* @var $column \ZfcDatagrid\Column\AbstractColumn */

			$options = [
				'name'  => (string) $column->getUniqueId(),
				'index' => (string) $column->getUniqueId(),
				//'label' => $this->translate((string) $column->getLabel()),
				'label' => $translateMethod->invokeArgs($this, [(string) $column->getLabel()]),

				'width'    => $column->getWidth(),
				'hidden'   => (bool) $column->isHidden(),
				'sortable' => (bool) $column->isUserSortEnabled(),
				'search'   => (bool) $column->isUserFilterEnabled(),
			];

			/*
			 * Formatting
			 */
			$formatter = $this->getFormatter($column);
			//$formatter = $getFormatterMethod->invokeArgs($this, [$column]);
			if ($formatter != '') {
				$options['formatter'] = (string) $formatter;
			}

            $alignAlreadyDefined = false;
            if ($column->hasStyles()) {
                foreach ($column->getStyles() as $style) {
                    /** @var Column\Style\Align $style */
                    if (get_class($style) == Column\Style\Align::class) {
                        $options['align'] = $style->getAlignment();
                        $alignAlreadyDefined = true;
                        break;
                    }
                }
            }

			if (!$alignAlreadyDefined && $column->getType() instanceof Type\Number) {
				$options['align'] = (string) 'right';
			}

			/*
			 * Cellattr
			 */
			$rendererParameters = $column->getRendererParameters('jqGrid');
			/*if (isset($rendererParameters['cellattr'])) {
				$options['cellattr'] = (string) $rendererParameters['cellattr'];
			}
			if (isset($rendererParameters['classes'])) {
				$options['classes'] = (string) $rendererParameters['classes'];
			}
            if (isset($rendererParameters['editable'])) {
                $options['editable'] = (bool) $rendererParameters['editable'];
            }
            if (isset($rendererParameters['deletable'])) {
                $options['deletable'] = (bool) $rendererParameters['deletable'];
            }*/

            /**
             * ColModel attributes
             */
            foreach ($rendererParameters as $option => $value) {
                $options[$option] = $value;
            }


			/*
			 * Filtering
			 */
			//$searchoptions                = [];
			$searchoptions = isset($rendererParameters['searchoptions']) ? $rendererParameters['searchoptions'] : [];
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

			//if ($customOpt = $this->getCustomColumnModelOption($column)) {
			//	$options = array_merge($options, $customOpt);
				//\Zend\Debug\Debug::dump($options); die(__METHOD__);
			//}

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

    /**
     * @param Column\AbstractColumn $column
     *
     * @return string
     */
    protected function getFormatter(Column\AbstractColumn $column)
    {
        /*
         * User defined formatter
         */
        $rendererParameters = $column->getRendererParameters('jqGrid');
        if (isset($rendererParameters['formatter'])) {
            return $rendererParameters['formatter'];
        }

        /*
         * Formatter based on column options + styles
         */
        $formatter = '';

        $formatter .= implode(' ', $this->getStyles($column));

        switch (get_class($column->getType())) {
            case Type\PhpArray::class:
                $formatter .= 'cellvalue = \'<pre>\' + cellvalue.join(\'<br />\') + \'</pre>\';';
                break;
        }

        if ($column instanceof Column\Action) {
            $formatter .= ' cellvalue = cellvalue; ';
        }

        if ($formatter != '') {
            $prefix = 'function (cellvalue, options, rowObject) {';
            $suffix = ' return cellvalue; }';

            $formatter = $prefix . $formatter . $suffix;
        }

        return $formatter;
    }

    /**
     * @param Column\AbstractColumn $col
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function getStyles(Column\AbstractColumn $col)
    {
        $styleFormatter = [];

        /*
         * First all based on value (only one works) @todo
         */
        foreach ($col->getStyles() as $style) {
            $prepend = '';
            $append = '';

            /* @var $style Column\Style\AbstractStyle */
            foreach ($style->getByValues() as $rule) {
                $colString = $rule['column']->getUniqueId();
                switch ($rule['operator']) {
                    case Filter::EQUAL:
                        $operator = '==';
                        break;

                    case Filter::NOT_EQUAL:
                        $operator = '!=';
                        break;

                    case Filter::GREATER_EQUAL:
                        $operator = '>=';
                        break;

                    case Filter::GREATER:
                        $operator = '>';
                        break;

                    case Filter::LESS_EQUAL:
                        $operator = '<=';
                        break;

                    case Filter::LESS:
                        $operator = '<';
                        break;

                    default:
                        throw new \Exception('Currently not supported filter operation: "'.$rule['operator'].'"');
                }

                $valueString = ($rule['value'] instanceof Column\AbstractColumn)
                    ? 'rowObject.' . $rule['value']->getUniqueId()
                    : "'" . $rule['value'] . "'";
                    //: $rule['value'];
                $prepend .= 'if (rowObject.' . $colString . ' ' . $operator . ' ' . $valueString . ') {';
                $append .= '}';
            }

            $styleString = '';
            switch (get_class($style)) {
                case Column\Style\Bold::class:
                    $styleString = self::STYLE_BOLD;
                    break;

                case Column\Style\Italic::class:
                    $styleString = self::STYLE_ITALIC;
                    break;

                case Column\Style\Strikethrough::class:
                    $styleString = self::STYLE_STRIKETHROUGH;
                    break;

                case Column\Style\Color::class:
                    $styleString = sprintf(
                        'cellvalue = \'<span style="color: #%s;">\' + cellvalue + \'</span>\';',
                        $style->getRgbHexString()
                    );
                    break;

                case Column\Style\CSSClass::class:
                    $styleString = 'cellvalue = \'<span class="'.$style->getClass().'">\' + cellvalue + \'</span>\';';
                    break;

                case Column\Style\BackgroundColor::class:
                    // do NOTHING! this is done by loadComplete event...
                    // At this stage jqgrid haven't created the columns...
                    break;

                case Column\Style\Html::class:
                    // do NOTHING! just pass the HTML!
                    break;

                case Column\Style\Align::class:
                    // do NOTHING! we have to add the align style in the gridcell and not in a span!
                    break;

                default:
                    throw new \Exception('Not defined style: "'.get_class($style).'"');
                    break;
            }

            $styleFormatter[] = $prepend.$styleString.$append;
        }

        return $styleFormatter;
    }

}