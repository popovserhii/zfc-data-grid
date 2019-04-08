<?php
/**
 * Enter description here...
 *
 * @category Popov
 * @package Popov_ZfcDataDrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @datetime: 02.11.2016 7:21
 */
namespace Popov\ZfcDataGrid\Service\Progress;

use Doctrine\ORM\EntityManager;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Stagem\ZfcProgress\Service\ContextInterface;
use Popov\ZfcUser\Model\User;
use Popov\Simpler\SimplerHelper;
use Popov\ZfcEntity\Helper\ModuleHelper;
use Popov\ZfcEntity\Helper\EntityHelper;
use Popov\ZfcFields\Service\FieldsService;

/**
 * @method TranslatorInterface getTranslator()
 */
class DataGridContext implements ContextInterface, ObjectManagerAwareInterface
{
    use TranslatorAwareTrait;

    use ProvidesObjectManager;

    protected $event;

    protected $user;
    
    /** @var ModuleHelper */
    protected $moduleHelper;

    /** @var SimplerHelper */
    protected $simplerHelper;

    /** @var FieldsService */
    protected $fieldsService;

    public function __construct(ModuleHelper $moduleHelper, SimplerHelper $simplerHelper, FieldsService $fieldsService)
    {
        $this->moduleHelper = $moduleHelper;
        $this->simplerHelper = $simplerHelper;
        $this->fieldsService = $fieldsService;
    }

    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    /**
     * @return EntityHelper
     */
    public function getEntityHelper()
    {
        return $this->moduleHelper->getEntityHelper();
    }

    /**
     * @return SimplerHelper
     */
    public function getSimplerHelper()
    {
        return $this->simplerHelper;
    }

    public function getFieldsService()
    {
        return $this->fieldsService;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getItem()
    {
        return $this->event->getTarget();
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getExtra()
    {
        return [];
    }

    public function getDescription()
    {
        return '';
    }

    public function getMessage()
    {
        /** @var EntityManager $om */
        $om = $this->getObjectManager();
        $uow = $om->getUnitOfWork();
        //$uow->computeChangeSets(); // do not compute changes if inside a listener

        $item = $this->getItem();
        $class = $om->getClassMetadata(get_class($item));
        $uow->recomputeSingleEntityChangeSet($class, $item);

        $changeSet = $uow->getEntityChangeSet($item);

        $message = [];
        if ($changeSet) {
            $translator = $this->getTranslator();
            $module = $this->getModuleHelper()->setRealContext($item)->getModule();
            $entity = $this->getEntityHelper()->setContext($item)->getEntity();
            $fields = $this->getSimplerHelper()
                ->setContext($this->getFieldsService()->getAllByEntity($entity))
                ->asAssociate('mnemo');

            $message[] = $translator->translate(
                ucfirst($entity->getMnemo()) . ' was edited',
                $module->getName(),
                $translator->getFallbackLocale()
            );
            $message[] = $translator->translate(
                'Changed fields',
                $this->getTranslatorTextDomain(),
                $translator->getFallbackLocale()
            ) . ':';

            foreach ($changeSet as $field => $set) {
                $fieldName = isset($fields[$field]) ? $fields[$field]->getName() : ucfirst($field);
                $template = $translator->translate(
                    '%s from %s to %s',
                    $this->getTranslatorTextDomain(),
                    $translator->getFallbackLocale()
                );
                $message[] = sprintf(
                    $template,
                    $fieldName,
                    $this->prepareSetValue($set[0]),
                    $this->prepareSetValue($set[1])
                );
            }
        }

        return implode("\n", $message);
    }

    protected function prepareSetValue($value)
    {
        if (!is_scalar($value) && $this->getEntityHelper()->isDoctrineObject($value)) {
            $value = $value->getId();
        }

        return $value;
    }
}