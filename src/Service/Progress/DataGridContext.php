<?php
/**
 * Enter description here...
 *
 * @category Agere
 * @package Agere_<package>
 * @author Popov Sergiy <popov@agere.com.ua>
 * @datetime: 02.11.2016 7:21
 */
namespace Agere\ZfcDataGrid\Service\Progress;

use Zend\Mvc\I18n\Translator;
use Zend\I18n\Translator\TranslatorAwareTrait;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;
use Agere\Progress\Service\ContextInterface;
use Magere\Entity\Controller\Plugin\ModulePlugin;
use Magere\Entity\Controller\Plugin\EntityPlugin;
use Magere\Fields\Service\FieldsService;
use Agere\Simpler\Plugin\SimplerPlugin;

/**
 * @method Translator getTranslator()
 */
class DataGridContext implements ContextInterface, ObjectManagerAwareInterface
{
    use TranslatorAwareTrait;

    use ProvidesObjectManager;

    protected $event;

    /** @var ModulePlugin */
    protected $modulePlugin;

    /** @var SimplerPlugin */
    protected $simplerPlugin;

    /** @var FieldsService */
    protected $fieldsService;

    public function __construct(ModulePlugin $modulePlugin, SimplerPlugin $simplerPlugin, FieldsService $fieldsService)
    {
        $this->modulePlugin = $modulePlugin;
        $this->simplerPlugin = $simplerPlugin;
        $this->fieldsService = $fieldsService;
    }

    public function getModulePlugin()
    {
        return $this->modulePlugin;
    }

    /**
     * @return EntityPlugin
     */
    public function getEntityPlugin()
    {
        return $this->modulePlugin->getEntityPlugin();
    }

    public function getSimplerPlugin()
    {
        return $this->simplerPlugin;
    }

    public function getFieldsService()
    {
        return $this->fieldsService;
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

    public function getMessage()
    {
        $om = $this->getObjectManager();
        $uow = $om->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener
        $changeSet = $uow->getEntityChangeSet($item = $this->getItem());

        $message = [];
        if ($changeSet) {
            $translator = $this->getTranslator();
            $module = $this->getModulePlugin()->setRealContext($item)->getRealModule();
            $entity = $this->getEntityPlugin()->setContext($item)->getEntity();
            $fields = $this->getSimplerPlugin()
                ->setContext($this->getFieldsService()->getAllByEntity($entity))
                ->asAssociate('mnemo');

            $message[] = $translator->translate(
                ucfirst($entity->getMnemo()) . ' edited',
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
                $message[] = sprintf($template, $fieldName, $set[0], $set[1]);
            }
        }

        return implode("\n", $message);
    }
}