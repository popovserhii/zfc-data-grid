<?php
/**
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 04.04.2016 0:19
 */
namespace Popov\ZfcDataGrid\Action\Admin\Factory;

use Doctrine\ORM\EntityManager;
use Popov\ZfcDataGrid\GridHelper;
use Zend\Stdlib\Exception;
use Psr\Container\ContainerInterface;
use Popov\ZfcCurrent\CurrentHelper;
use Popov\ZfcEntity\Helper\EntityHelper;
use Popov\ZfcDataGrid\Hydrator\DoctrineObject;
use Popov\ZfcDataGrid\Action\Admin\ModifyAction;

class ModifyActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $currentHelper = $container->get(CurrentHelper::class);

        #$router = $container->get('Router');
        #$request = $container->get('Request');

        $params = $currentHelper->currentRouteParams();
        //if (!($gridId = $params['id'])) {
        if (!($gridId = $params['grid'])) {
            throw new Exception\InvalidArgumentException(
                'Route key "grid" must be set for correct work edit functionality'
            );
        }

        $girdHelper = $container->get(GridHelper::class);
        $entityHelper = $container->get(EntityHelper::class);

        //$hydrator = new DoctrineObject($om);
        //$domainService = $container->get(ucfirst($gridId) . 'Service');

        $action = new ModifyAction($girdHelper, $entityHelper);

        return $action;
    }
}