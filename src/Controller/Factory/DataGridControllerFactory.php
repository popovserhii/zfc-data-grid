<?php
/**
 * @category Agere
 * @package Agere_DataGrid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 04.04.2016 0:19
 */
namespace Agere\ZfcDataGrid\Controller\Factory;

use Agere\ZfcDataGrid\Controller\DataGridController;
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
        //$modulePlugin = $cpm->get('module');
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

        if (!($gridId = $route->getParam('grid'))) {
            throw new Exception\InvalidArgumentException(
                'Route "grid" key must be set for correct work edit functionality'
            );
        }

        $domainService = $sm->get(ucfirst($gridId) . 'Service');

        $controller = new DataGridController($domainService);
        //$controller->setServiceManager($sm);

        return $controller;
    }
}