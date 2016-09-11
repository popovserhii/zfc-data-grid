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
use Zend\Stdlib\Exception;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Agere\Core\Service\DomainServiceInterface;

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

    public function editAction()
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

    public function delOperation()
    {
        $request = $this->getRequest();
        $route = $this->getEvent()->getRouteMatch();
        $domainService = $this->getDomainService();

        $om = $domainService->getObjectManager();
        $items = $domainService->getRepository()->findBy(['id' => explode(',', $request->getPost('id'))]);

        foreach ($items as $item) {
            $om->remove($item);
        }
        $om->flush();

        if ($request->isXmlHttpRequest()) {
            // only ajax processing
            //$this->getResponse()->setContent(Json::encode('Покупатель успешно сохранен'));
            $this->getResponse()->setContent('Selected items successfully deleted');

            return $this->getResponse();
        }
    }
}