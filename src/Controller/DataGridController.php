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
use Popov\ZfcEntity\Controller\Plugin\EntityPlugin;
use Popov\ZfcEntity\Controller\Plugin\ModulePlugin;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Popov\ZfcCore\Service\DomainServiceInterface;
use Popov\ZfcEntity\Model\Entity;

/**
 * @method GridPlugin grid()
 * @method EntityPlugin entity()
 * @method ModulePlugin module($context = null)
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
        return $this->editOperation();
    }

    public function editOperation()
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
                $params = ['context' => $this, 'gridData' => $gridData, 'entity' => $entity];
                $this->getEventManager()->trigger($operation . '.on', $item, $params);
                $items[] = $item->exchangeArray($entityData);
                $this->getEventManager()->trigger($operation, $item, $params);
            }
        }

        $om->flush();
        $this->getEventManager()->trigger($operation . '.post', $items, ['context' => $this]);

        return (new JsonModel([
            'message' => sprintf('Items successfully have been %sed', $operation),
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