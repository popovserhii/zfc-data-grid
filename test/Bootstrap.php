<?php

namespace PopovTest\ZfcDataGrid;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

ini_set('display_errors', 'on');
error_reporting(-1);

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $zfModulePaths = [dirname(dirname(__DIR__))];
        if (($path = static::findParentPath('vendor'))) {
            $zfModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zfModulePaths[0]) {
            $zfModulePaths[] = $path;
        }

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $testConfig = [
            'module_listener_options' => [
                'module_paths' => $zfModulePaths,
            ],
            'modules' => [
                'ZfcDatagrid',
                'Popov\ZfcCurrent',
                'Popov\ZfcDataGrid',
            ],
        ];

        //$applicationConfig = require(__DIR__ . '/../config/application.config.php.sample');
        $applicationConfig = [];
        $configuration = ArrayUtils::merge($testConfig, $applicationConfig);

        // if NEED use full project configuration
        #include $path . '/../init_autoloader.php';
        #self::$config = include $path . '/../config/application.config.php';
        #\Zend\Mvc\Application::init(self::$config);
        #self::$sm = self::getServiceManager(self::$config);

        // if DON'T NEED to use custom service manager
        #$serviceManager = new ServiceManager(new ServiceManagerConfig());
        #$serviceManager->setService('ApplicationConfig', $config);
        #$serviceManager->get('ModuleManager')->loadModules();
        #static::$serviceManager = $serviceManager;

        // if NEED to use custom service manager
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : [];
        $smConfig = new ServiceManagerConfig($smConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');
        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }

        return $dir . '/' . $path;
    }
}

Bootstrap::init();
Bootstrap::chroot();