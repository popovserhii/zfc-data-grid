<?php

namespace Agere\ZfcDataGrid\Block;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\Paginator\Paginator;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\PrepareData;
use ArrayIterator;

class AgereDatagrid extends DataGrid
{
    /**
     * Load the data
     */
    public function loadData($paginatorClass = Paginator::class, $prepareDataClass = PrepareData::class)
    {
        if (true === $this->isDataLoaded) {
            return true;
        }

        if ($this->isInit() !== true) {
            throw new \Exception('The init() method has to be called, before you can call loadData()!');
        }

        if ($this->hasDataSource() === false) {
            throw new \Exception('No datasource defined! Please call "setDataSource()" first"');
        }

        /**
         * Apply cache
         */
        $renderer = $this->getRenderer();

        /**
         * Step 1) Apply needed columns + filters + sort
         * - from Request (HTML View) -> and save in cache for export
         * - or from cache (Export PDF / Excel) -> same view like HTML (without LIMIT/Pagination)
         */
        {
            /**
             * Step 1.1) Only select needed columns (performance)
             */
            $this->getDataSource()->setColumns($this->getColumns());

            /**
             * Step 1.2) Sorting
             */
            foreach ($renderer->getSortConditions() as $condition) {
                $this->getDataSource()->addSortCondition($condition['column'], $condition['sortDirection']);
            }

            /**
             * Step 1.3) Filtering
             */
            //\Zend\Debug\Debug::dump([$renderer->getFilters(), __METHOD__]);
            foreach ($renderer->getFilters() as $filter) {
                $this->getDataSource()->addFilter($filter);
            }
        }

        /*
         * Step 2) Load the data (Paginator)
         */
        {
            /** @var \ZfcDatagrid\DataSource\Doctrine2 $dataSource */
            $dataSource = $this->getDataSource();
            $dataSource->execute();

            $paginatorAdapter = $dataSource->getPaginatorAdapter();

            \Zend\Paginator\Paginator::setDefaultScrollingStyle('Sliding');

            $this->paginator = new $paginatorClass($paginatorAdapter);
            if ($this->paginator instanceof EventManagerAwareInterface) {
                $this->paginator->setEventManager($this->getServiceLocator()->get('EventManager'));
            }
            $this->paginator->setCurrentPageNumber($renderer->getCurrentPageNumber());
            $this->paginator->setItemCountPerPage($renderer->getItemsPerPage($this->getDefaultItemsPerPage()));

            /* @var $currentItems \ArrayIterator */
            $data = $this->paginator->getCurrentItems();
            if (! is_array($data)) {
                if ($data instanceof \Zend\Db\ResultSet\ResultSet) {
                    $data = $data->toArray();
                } elseif ($data instanceof ArrayIterator) {
                    $data = $data->getArrayCopy();
                } else {
                    if (is_object($data)) {
                        $add = get_class($data);
                    } else {
                        $add = '[no object]';
                    }
                    throw new \Exception(
                        sprintf('The paginator returned an unknown result: %s (allowed: \ArrayIterator or a plain php array)', $add)
                    );
                }
            }
        }

        /*
         * check if the export is enabled
         * Save cache
         */
        if ($this->getOptions()['settings']['export']['enabled'] && $renderer->isExport() === false) {
            $cacheData = [
                'sortConditions' => $renderer->getSortConditions(),
                'filters'        => $renderer->getFilters(),
                'currentPage'    => $this->getPaginator()->getCurrentPageNumber(),
            ];
            $success = $this->getCache()->setItem($this->getCacheId(), $cacheData);
            if ($success !== true) {
                /** @var \Zend\Cache\Storage\Adapter\FilesystemOptions $options */
                $options = $this->getCache()->getOptions();
                throw new \Exception(
                    sprintf(
                        'Could not save the datagrid cache. Does the directory "%s" exists and is writeable? CacheId: %s',
                        $options->getCacheDir(),
                        $this->getCacheId()
                    )
                );
            }
        }

        /*
         * Step 3) Format the data - Translate - Replace - Date / time / datetime - Numbers - ...
         */
        $prepareData = new $prepareDataClass($data, $this->getColumns());
        $prepareData->setRendererName($this->getRendererName());
        if ($this->hasTranslator()) {
            $prepareData->setTranslator($this->getTranslator());
        }
        $prepareData->prepare();
        $this->preparedData = $prepareData->getData();

        $this->isDataLoaded = true;
    }

    public function render($paginatorClass = Paginator::class, $prepareDataClass = PrepareData::class)
    {
        if ($this->isDataLoaded() === false) {
            $this->loadData($paginatorClass, $prepareDataClass);
        }
        parent::render();
    }
}