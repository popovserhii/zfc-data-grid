<?php
/**
 * Zfc Data Grid Controller
 *
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 10.09.2016 20:12
 */
namespace Popov\ZfcDataGrid\Action\Admin;

use Popov\ZfcEntity\Helper\EntityHelper;
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Popov\ZfcCore\Service\DomainServiceInterface;
use Popov\ZfcEntity\Model\Entity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Fig\Http\Message\RequestMethodInterface;

class ModifyAction implements MiddlewareInterface, RequestMethodInterface
{
    /**
     * @var DomainServiceInterface
     */
    protected $domainService;

    /**
     * @var EntityHelper
     */
    protected $entityHelper;

    public function __construct(/*DomainServiceInterface*/ $entityService, EntityHelper $entityHelper)
    {
        $this->domainService = $entityService;
        $this->entityHelper = $entityHelper;
    }

    public function getDomainService()
    {
        return $this->domainService;
    }

    public function getEntityHelper()
    {
        return $this->entityHelper;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== self::METHOD_POST) {
            return $handler->handle($request);
        }

        //$om = $sm->get('Doctrine\ORM\EntityManager');
        //$params = $request->getParsedBody();
        if (!method_exists($this, $method = $request->getAttribute('oper') . 'Operation')) {
            throw new Exception\RuntimeException(sprintf(
                'Operation "%s" not supported. Use only edit or delete operation', $request->getAttribute('oper')
            ));
        }

        return $this->{$method}($request);
    }

    public function addOperation($request)
    {
        return $this->editOperation($request);
    }

    public function editOperation($request)
    {
        $domainService = $this->getDomainService();
        $om = $domainService->getObjectManager();
        $operation = $request->getAttribute('oper');

        $gridData = $this->grid()->prepareExchangeData($request);

        $items = [];
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $entityData) {
                $item = $this->entity()->find($itemId, $entity, EntityHelper::CREATE_EMPTY);
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

        return (new JsonModel([
            'message' => 'Edited items successfully updated',
        ]));
    }
}