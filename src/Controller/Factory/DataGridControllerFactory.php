<?php
/**
 * @category Popov
 * @package Popov_DataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 04.04.2016 0:19
 */
namespace Popov\ZfcDataGrid\Controller\Factory;

use Popov\ZfcDataGrid\Controller\DataGridController;
use Zend\Stdlib\Exception;

class DataGridControllerFactory
{
    public function __invoke($cm)
    {
        $sm = $cm->getServiceLocator();

        $cpm = $sm->get('ControllerPluginManager');
        //$vhm = $sm->get('ViewHelperManager');
        /** @var BlockPluginManager $bpm */
        //$bpm = $sm->get('BlockPluginManager');
        //$urlPlugin = $cpm->get('url');
        /** @var Current $currentPlugin */
        //$currentPlugin = $cpm->get('current');
        $entityPlugin = $cpm->get('entity');
        /** @var \Zend\Mvc\Router\RouteMatch $route */
        // Important get route from current plugin for correct work of forward
        //$route = $currentPlugin->currentRoute();
        // Important get route from current controller for correct work of forward
        //$route = $currentPlugin->getController()->getEvent()->getRouteMatch();

        $router = $sm->get('Router');
        $request = $sm->get('Request');

        // Get the router match
        $route = $router->match($request);
        //$this->slug = $route->getParam("slug");

        if (!($gridId = $route->getParam('id'))) {
            throw new Exception\InvalidArgumentException(
                'Route key "grid" must be set for correct work edit functionality'
            );
        }

        $domainService = $sm->get(ucfirst($gridId) . 'Service');

        $controller = new DataGridController($domainService, $entityPlugin);
        //$controller->setServiceManager($sm);

        return $controller;
    }
}