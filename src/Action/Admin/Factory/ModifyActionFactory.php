<?php
/**
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 04.04.2016 0:19
 */
namespace Popov\ZfcDataGrid\Action\Admin\Factory;

use Popov\ZfcCurrent\CurrentHelper;
use Popov\ZfcDataGrid\Action\Admin\ModifyAction;
use Popov\ZfcDataGrid\Controller\DataGridController;
use Popov\ZfcEntity\Helper\EntityHelper;
use Psr\Container\ContainerInterface;
use Zend\Stdlib\Exception;

class ModifyActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityHelper = $container->get(EntityHelper::class);
        $currentHelper = $container->get(CurrentHelper::class);

        #$router = $container->get('Router');
        #$request = $container->get('Request');

        // Get the router match
        #$route = $router->match($request);
        //$this->slug = $route->getParam("slug");

        $params = $currentHelper->currentRouteParams();
        if (!($gridId = $params['id'])) {
            throw new Exception\InvalidArgumentException(
                'Route key "grid" must be set for correct work edit functionality'
            );
        }

        $domainService = $container->get(ucfirst($gridId) . 'Service');

        $action = new ModifyAction($domainService, $entityHelper);
        //$controller->setServiceManager($sm);

        return $action;
    }
}