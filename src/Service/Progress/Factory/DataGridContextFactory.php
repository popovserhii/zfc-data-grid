<?php
/**
 * Progress Service Factory
 *
 * @category Agere
 * @package Agere_ZfcDataGrid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 03.11.16 19:01
 */
namespace Agere\ZfcDataGrid\Service\Progress\Factory;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\PluginManager;
use Agere\ZfcDataGrid\Service\Progress\DataGridContext;
use Magere\ZfcEntity\Controller\Plugin\ModulePlugin;
use Magere\Fields\Service\FieldsService;
use Agere\Simpler\Plugin\SimplerPlugin;

class DataGridContextFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var PluginManager $cpm */
        $cpm = $container->get('ControllerPluginManager');
        /** @var FieldsService $fieldsService */
        $fieldsService = $container->get('FieldsService');
        /** @var ModulePlugin $modulePlugin */
        $modulePlugin = $cpm->get('module');
        /** @var SimplerPlugin $simplerPlugin */
        $simplerPlugin = $cpm->get('simpler');

        return (new DataGridContext($modulePlugin, $simplerPlugin, $fieldsService));
    }
}