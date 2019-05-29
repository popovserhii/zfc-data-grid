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
     * Convert jqGrid data format to Form format
     *
     * @param array $params
     * @return array
     */
    public function prepareExchangeData($params)
    {
        $gridMnemo = $this->getCurrentGridId();

        if (isset($params['id'])) {
            $params[$gridMnemo . '_id'] = $params['id'];
        }
        unset($params['id']);
        unset($params['oper']);

        $gridData = [];
        foreach ($params as $name => $value) {
            list($entityMnemo, $field) = explode('_', $name);
            if (isset($params[$entityMnemo . '_id'])) {
                if ($gridMnemo === $entityMnemo) {
                    $gridData[$entityMnemo][$params[$entityMnemo . '_id']][$field] = $value;
                } elseif ($gridMnemo !== $entityMnemo) {
                    // Received grouped collection, add it is a child of main grid
                    $ids = explode(',', $value);
                    $ids = array_filter($ids);
                    if ($counter = count($ids)) {
                        $ids = ($counter === 1) ? array_shift($ids) : $ids;
                        $gridData[$gridMnemo][$params[$gridMnemo . '_id']][$entityMnemo][$field] = $ids;
                    }
                }
            } elseif ($gridMnemo === $entityMnemo) {
                $gridData[$entityMnemo][$params[$gridMnemo . '_id']][$field] = $value;
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