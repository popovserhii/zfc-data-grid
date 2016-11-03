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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Filter\Word\CamelCaseToDash;
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Agere\Core\Service\DomainServiceInterface;
use Magere\Entity\Model\Entity;

/**
 * @method \Magere\Entity\Controller\Plugin\EntityPlugin entity()
 * @method \Magere\Entity\Controller\Plugin\ModulePlugin module($context = null)
 */
class DataGridController extends AbstractActionController
{
    /**
     * @var DomainServiceInterface
     */
    protected $domainService;

    public function __construct(/*DomainServiceInterface*/ $entityService)
    {
        $this->domainService = $entityService;
    }

    public function getDomainService()
    {
        return $this->domainService;
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

    public function editOperation()
    {
        $request = $this->getRequest();
        $route = $this->getEvent()->getRouteMatch();
        $domainService = $this->getDomainService();

        $om = $domainService->getObjectManager();
        //$items = $domainService->getRepository()->findBy(['id' => explode(',', $request->getPost('id'))]);

        $gridData = [];
        foreach ($request->getPost() as $name => $value) {
            if (in_array($name, ['id', 'oper']) || (substr($name, -3, 3) === '_id')) { // skip specialized keywords
                continue;
            }

            $gridMnemo = strtolower((new CamelCaseToDash())->filter($route->getParam('grid')));
            list($moduleMnemo, $field) = explode('_', strtolower((new CamelCaseToDash())->filter($name)));
            if ($itemId = $request->getPost($moduleMnemo . '_id')) {
                $gridData[$moduleMnemo][$itemId][$field] = $value;
            } elseif ($gridMnemo === $moduleMnemo) {
                $itemId = $request->getPost('id');
                $gridData[$moduleMnemo][$itemId][$field] = $value;
            }
        }

        $items = [];
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $entityData) {
                $items[] = $item = $this->entity()->find($itemId, $entity)->exchangeArray($entityData);
                $this->getEventManager()->trigger('edit', $item, ['context' => $this]);
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
            $om->remove($item);
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