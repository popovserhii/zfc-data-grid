<?php
/**
 * Zfc Data Grid Controller
 *
 * @category Agere
 * @package Agere_ZfcDataGrid
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 10.09.2016 20:12
 */
namespace Agere\ZfcDataGrid\Controller;

use Agere\ZfcDataGrid\Controller\Plugin\GridPlugin;
use Magere\Entity\Controller\Plugin\EntityPlugin;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Agere\Core\Service\DomainServiceInterface;
use Magere\Entity\Model\Entity;

/**
 * @method GridPlugin grid()
 * @method \Magere\Entity\Controller\Plugin\EntityPlugin entity()
 * @method \Magere\Entity\Controller\Plugin\ModulePlugin module($context = null)
 */
class DataGridController extends AbstractActionController
{
    /**
     * @var DomainServiceInterface
     */
    protected $domainService;

    /** @var EntityPlugin */
    protected $entityPlugin;

    public function __construct(/*DomainServiceInterface*/ $entityService, EntityPlugin $entityPlugin)
    {
        $this->domainService = $entityService;
        $this->entityPlugin = $entityPlugin;
    }

    public function getDomainService()
    {
        return $this->domainService;
    }

    public function getEntityPlugin()
    {
        return $this->entityPlugin;
    }

    public function modifyAction()
    {
        if (!($request = $this->getRequest()) && !$request->isPost()) {
            return [];
        }

        //$om = $sm->get('Doctrine\ORM\EntityManager');
        if (!method_exists($this, $method = $request->getPost('oper') . 'Operation')) {
            throw new Exception\RuntimeException(sprintf(
                'Operation "%s" not supported. Use only edit or delete operation', $request->getPost('oper')
            ));
        }

        return $this->{$method}();
    }

    public function addOperation()
    {
        $request = $this->getRequest();
        $domainService = $this->getDomainService();
        $om = $domainService->getObjectManager();
        $operation = $request->getPost('oper');

        $gridData = $this->grid()->prepareExchangeData($request);

        $items = [];
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $entityData) {
                $item = $this->entity()->find($itemId, $entity, EntityPlugin::CREATE_EMPTY);
                $params = ['context' => $this, 'gridData' => $gridData];
                $this->getEventManager()->trigger($operation . '.on', $item, $params);
                $items[] = $item->exchangeArray($entityData);
                $this->getEventManager()->trigger($operation, $item, $params);
            }
        }

        $om->flush();
        $this->getEventManager()->trigger($operation . '.post', $items, ['context' => $this]);

        return (new JsonModel([
            'message' => 'Edited items successfully updated',
        ]));
    }

    public function editOperation()
    {
        $request = $this->getRequest();
        $route = $this->getEvent()->getRouteMatch();
        $domainService = $this->getDomainService();
        //$entityPlugin = $this->getEntityPlugin();

        $om = $domainService->getObjectManager();
        //$items = $domainService->getRepository()->findBy(['id' => explode(',', $request->getPost('id'))]);

        $gridData = [];
        foreach ($request->getPost() as $name => $value) {
            if (in_array($name, ['id', 'oper']) || (substr($name, -3, 3) === '_id')) { // skip specialized keywords
                continue;
            }

            $filter = new CamelCaseToDash();
            //$gridMnemo = $route->getParam('grid');
            list($moduleMnemo, $field) = explode('_', $name);
            $moduleMnemoAlias = strtolower($filter->filter($moduleMnemo));
            //$moduleMnemoAlias = $entityPlugin->toAlias($moduleMnemo);
            //$gridMnemo = strtolower($filter->filter($route->getParam('grid')));
            $gridMnemo = $route->getParam('grid');
            if ($itemId = $request->getPost($moduleMnemo . '_id')) {
                $gridData[$moduleMnemoAlias][$itemId][$field] = $value;
            } elseif ($gridMnemo === $moduleMnemo) {
                $itemId = $request->getPost('id');
                $gridData[$moduleMnemoAlias][$itemId][$field] = $value;
            }
        }

        $items = [];
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $entityData) {
                $item = $this->entity()->find($itemId, $entity);
                $params = ['context' => $this, 'gridData' => $gridData];
                $this->getEventManager()->trigger('edit.on', $item, $params);
                $items[] = $item->exchangeArray($entityData);
                $this->getEventManager()->trigger('edit', $item, $params);
            }
        }

        $om->flush();
        $this->getEventManager()->trigger('edit.post', $items, ['context' => $this]);



        /*if ($request->isXmlHttpRequest()) {
            // only ajax processing
            //$this->getResponse()->setContent(Json::encode('Покупатель успешно сохранен'));
            $this->getResponse()->setContent('Edited items successfully updated');

            return $this->getResponse();
        }*/

        return (new JsonModel([
            'message' => 'Edited items successfully updated',
        ]));
    }

    public function delOperation()
    {
        $request = $this->getRequest();
        //$route = $this->getEvent()->getRouteMatch();
        $domainService = $this->getDomainService();

        $om = $domainService->getObjectManager();
        $items = $domainService->getRepository()->findBy(['id' => explode(',', $request->getPost('id'))]);

        foreach ($items as $item) {
            $params = ['context' => $this];
            $this->getEventManager()->trigger('delete.on', $item, $params);
            $om->remove($item);
            $this->getEventManager()->trigger('delete', $item, $params);
        }
        $om->flush();

        /*if ($request->isXmlHttpRequest()) {
            // only ajax processing
            //$this->getResponse()->setContent(Json::encode('Покупатель успешно сохранен'));
            $this->getResponse()->setContent('Selected items successfully deleted');

            return $this->getResponse();
        }*/
        return (new JsonModel([
            'message' => 'Edited items successfully updated',
        ]));
    }
}