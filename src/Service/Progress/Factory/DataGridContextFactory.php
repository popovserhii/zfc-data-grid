<?php
/**
 * Progress Service Factory
 *
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 03.11.16 19:01
 */
namespace Popov\ZfcDataGrid\Service\Progress\Factory;

use Popov\ZfcFields\Service\FieldsService;
use Psr\Container\ContainerInterface;
use Popov\Simpler\SimplerHelper;
use Popov\ZfcEntity\Helper\ModuleHelper;
use Popov\ZfcDataGrid\Service\Progress\DataGridContext;

class DataGridContextFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var FieldsService $fieldsService */
        $fieldsService = $container->get(FieldsService::class);
        /** @var ModuleHelper $modulePlugin */
        $modulePlugin = $container->get(ModuleHelper::class);
        /** @var SimplerHelper $simplerPlugin */
        $simplerPlugin = $container->get(SimplerHelper::class);

        return (new DataGridContext($modulePlugin, $simplerPlugin, $fieldsService));
    }
}