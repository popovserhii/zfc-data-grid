<?php
namespace Agere\ZfcDataGrid\Block\Factory;

use Agere\ZfcDataGrid\Block\AgereDatagrid;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcDatagrid\Datagrid;

class DatagridFactory extends \ZfcDatagrid\Service\DatagridFactory
{
    /**
     *
     * @param  ServiceLocatorInterface $sm
     * @return Datagrid
     * @throws InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        $config = $sm->get('config');
        if (! isset($config['ZfcDatagrid'])) {
            throw new InvalidArgumentException('Config key "ZfcDatagrid" is missing');
        }
        /* @var $application \Zend\Mvc\Application */
        $application = $sm->get('application');

        $grid = new AgereDatagrid();
        $grid->setOptions($config['ZfcDatagrid']);
        $grid->setMvcEvent($application->getMvcEvent());
        if ($sm->has('translator') === true) {
            $grid->setTranslator($sm->get('translator'));
        }
        $grid->init();

        return $grid;
    }
}
