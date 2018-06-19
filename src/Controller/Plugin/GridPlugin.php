<?php
/**
 * Plugin which add form element to grid.
 * This allow add status buttons to different fieldsets in form
 *
 * @category Popov
 * @package Popov_Grid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 26.03.16 19:37
 */

namespace Popov\ZfcDataGrid\Controller\Plugin;

use Popov\ZfcDataGrid\GridHelper;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class GridPlugin extends AbstractPlugin
{
    /**
     * @var GridHelper
     */
    protected $gridHelper;

    public function __construct(GridHelper $gridHelper)
    {
        $this->gridHelper = $gridHelper;
    }

    public function __invoke()
    {
        $params = func_get_args();

        return call_user_func_array($this->gridHelper, $params);
    }
}