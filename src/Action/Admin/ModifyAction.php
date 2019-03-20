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
use Zend\Diactoros\Response\JsonResponse;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Popov\ZfcCore\Service\DomainServiceInterface;
use Popov\ZfcEntity\Model\Entity;
use Popov\ZfcEntity\Helper\EntityHelper;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function __construct(GridHelper $gridHelper, EntityHelper $entityHelper)
    {
        //$this->domainService = $entityService;
        $this->gridHelper = $gridHelper;
        $this->entityHelper = $entityHelper;
    }

    public function getGridHelper()
    {
        return $this->gridHelper;
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
        $params = $request->getParsedBody();
        $operation = $params['oper'];

        $gridData = $gridHelper->prepareExchangeData($request);

        $items = [];
        $entities = $om->getRepository(Entity::class)->findBy(['mnemo' => array_keys($gridData)]);
        foreach ($entities as $entity) {
            foreach ($gridData[$entity->getMnemo()] as $itemId => $entityData) {
                $item = $entityHelper->find($itemId, $entity, EntityHelper::CREATE_EMPTY);
                $params = ['context' => $this, 'gridData' => $params, 'entity' => $entity];
                // @todo fix ProgressContext
                #$this->getEventManager()->trigger($operation . '.on', $item, $params);

                // @todo Hardcode. Implement Doctrine Hydrator
                foreach ($entityData as $property => $value) {
                    if (method_exists($item, $method = 'set' . ucfirst($property))) {
                        $item->{$method}($value);
                    } elseif (method_exists($item, $method = 'add' . ucfirst($property))) {
                        $getMethod = 'get' . ucfirst($property) . 's';
                        $subEntity = $entityHelper->getBy($property, 'mnemo');
                        $item->{$getMethod}()->clear(); // this don't work
                        //$removeMethod = 'remove' . ucfirst($property);
                        if (!empty($value)) {
                            $value = is_array($value) ? $value : [$value];

                            /*foreach ($item->g as $subValue) {
                                $item->{$removeMethod}($om->find($subEntity->getNamespace(), $subValue));
                            }*/

                            foreach ($value as $subValue) {
                                $item->{$method}($om->find($subEntity->getNamespace(), $subValue));
                            }
                        }
                    }
                }

                #$this->getEventManager()->trigger($operation, $item, $params);
            }
        }

        $om->flush();
        #$this->getEventManager()->trigger($operation . '.post', $items, ['context' => $this]);

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