<?php
/**
 * Helper which add form element to grid.
 * This allow add status buttons to different fieldsets in form
 *
 * @category Popov
 * @package Popov_ZfcGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 26.03.16 19:37
 */

namespace Popov\ZfcDataGrid;

use Zend\Stdlib\Exception;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Http\PhpEnvironment\Request;
use Popov\ZfcCurrent\CurrentHelper;

class GridHelper
{
    /**
     * @var CurrentHelper
     */
    protected $currentHelper;

    public function __construct(CurrentHelper $currentHelper)
    {
        $this->currentHelper = $currentHelper;
    }

    public function getCurrentHelper()
    {
        return $this->currentHelper;
    }

    public function getCurrentGridId()
    {
        $routeParams = $this->currentHelper->currentRouteParams();
        $gridMnemo = $routeParams['grid'] ?? null;

        return $gridMnemo;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function prepareExchangeData($request)
    {
        //$route = $this->getRoute();
        //$params = $request->getPost();
        //$routeParams = $this->currentHelper->currentRouteParams();
        $gridMnemo = $this->getCurrentGridId();
        $params = $request->getParsedBody();
        #$filter = new CamelCaseToDash();
        $gridData = [];
        foreach ($params as $name => $value) {
            if (in_array($name, ['id', 'oper']) /*|| (substr($name, -3, 3) === '_id')*/) { // skip specialized keywords
                continue;
            }
            list($moduleMnemo, $field) = explode('_', $name);
            //$moduleMnemoAlias = strtolower($filter->filter($moduleMnemo));
            $moduleMnemoAlias = $moduleMnemo;

            if (isset($params[$moduleMnemo . '_id'])) {
                if ($gridMnemo !== $moduleMnemo) { // received grouped collection, add it as child of main grid
                    $ids = explode(',', $value);
                    if (count($ids) <= 1) {
                        $ids = array_shift($ids);
                    }
                    $gridData[$gridMnemo][$params['id']][$moduleMnemoAlias] = $ids;
                } else {
                    $gridData[$moduleMnemoAlias][$params[$moduleMnemo . '_id']][$field] = $value;
                }
            } elseif ($gridMnemo === $moduleMnemo) {
                //$itemId = $params['id'];
                $gridData[$moduleMnemoAlias][$params['id']][$field] = $value;
            }
        }

        return $gridData;
    }

    protected function prepareIdentifier($value)
    {

    }

    public function __invoke()
    {
        return $this;
    }
}