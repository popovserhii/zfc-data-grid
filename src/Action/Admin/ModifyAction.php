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

use Popov\ZfcDataGrid\GridHelper;
use Popov\ZfcUser\Helper\UserHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
#use Psr\Http\Server\MiddlewareInterface;
#use Psr\Http\Server\RequestHandlerInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Fig\Http\Message\RequestMethodInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Stagem\Report\Model\Attribute;
use Zend\Diactoros\Response\JsonResponse;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Stdlib\Exception;
use Popov\ZfcCore\Service\DomainServiceInterface;
use Popov\ZfcEntity\Model\Entity;
use Popov\ZfcEntity\Helper\EntityHelper;
/**
 * @method EntityHelper entity()
 */
class ModifyAction implements MiddlewareInterface, RequestMethodInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     * @var DomainServiceInterface
     */
    //protected $domainService;

    /**
     * @var EntityHelper
     */
    protected $entityHelper;

    /**
     * @var GridHelper
     */
    protected $gridHelper;

    /**
     * @var DoctrineHydrator
     */
    protected $doctrineHydrator;

    public function __construct(GridHelper $gridHelper, EntityHelper $entityHelper, DoctrineHydrator $doctrineHydrator)
    {
        $this->gridHelper = $gridHelper;
        $this->entityHelper = $entityHelper;
        $this->doctrineHydrator = $doctrineHydrator;
    }

    public function getGridHelper()
    {
        return $this->gridHelper;
    }

    public function getEntityHelper()
    {
        return $this->entityHelper;
    }

    public function getDoctrineHydrator()
    {
        return $this->doctrineHydrator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() !== self::METHOD_POST) {
            return $handler->handle($request);
        }

        $params = $request->getParsedBody();
        if (!method_exists($this, $method = $params['oper'] . 'Operation')) {
            throw new Exception\RuntimeException(sprintf(
                'Operation "%s" not supported. Use only edit or delete operation', $params['oper']
            ));
        }

        $data = $this->{$method}($request);

        return new JsonResponse($data);
    }

    public function addOperation($request)
    {
        return $this->editOperation($request);
    }

    public function editOperation($request)
    {
        $gridHelper = $this->getGridHelper();
        $entityHelper = $this->getEntityHelper();
        $om = $entityHelper->getObjectManager();
        $doctrineHydrator = $this->getDoctrineHydrator();
        $params = $request->getParsedBody();
        $operation = $params['oper'];

        $gridData = $gridHelper->prepareExchangeData($params);

        /** @var Entity[] $entities */
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        $items = [];
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $itemData) {
                $item = $entityHelper->find($itemId, $entity, EntityHelper::CREATE_EMPTY);

                $params = ['context' => $this, 'gridData' => $params, 'entity' => $entity];
                $this->getEventManager()->trigger($operation . '.on', $item, $params);

                $item = $doctrineHydrator->hydrate($itemData, $item);

                $this->getEventManager()->trigger($operation, $item, $params);
            }
        }

        $om->flush();
        $this->getEventManager()->trigger($operation . '.post', $items, ['context' => $this]);

        return [
            'message' => sprintf('Items successfully have been %sed', $operation),
        ];
    }

    public function delOperation($request)
    {
        $gridHelper = $this->getGridHelper();
        $entityHelper = $this->getEntityHelper();
        $om = $entityHelper->getObjectManager();
        $params = $request->getParsedBody();

        /** @var Entity $entity */
        $entity = $om->getRepository(Entity::class)->findOneBy(['mnemo' => $gridHelper->getCurrentGridId()]);
        $items = $om->getRepository($entity->getNamespace())->findBy(['id' => explode(',', $params['id'])]);
        foreach ($items as $item) {
            $params = ['context' => $this];
            $this->getEventManager()->trigger('delete.on', $item, $params);
            $om->remove($item);
            $this->getEventManager()->trigger('delete', $item, $params);
        }
        $om->flush();

        return [
            'message' => 'Edited items successfully updated',
        ];
    }
}