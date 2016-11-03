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

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

class DataGridContext implements ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    protected $event;

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getItem()
    {
        return $this->event->getTarget();
    }

    public function getExtra()
    {
        return [];
    }

    public function getMessage()
    {
        $om = $this->getObjectManager();
        //$item = $om->find('My\Entity', 1);
        //$item->setTitle('Changed Title!');

        $uow = $om->getUnitOfWork();
        $uow->computeChangeSets(); // do not compute changes if inside a listener
        $changeset = $uow->getEntityChangeSet($this->getItem());

        $changedField = [];
        foreach ($changeset as $field => $set) {
            if (is_int($set[0])) {
                $set[1] = (int) $set[1];
            } elseif (is_float($set[0])) {
                $set[1] = (float) $set[1];
            } elseif (is_bool($set[1])) {
                $set[1] = (bool) $set[1];
            }

            if ($set[0] !== $set[1]) {
                $changedField[] = $field;
            }
        }

        $message = '';
        if (isset($changedField[0])) {
            $message = sprintf('Сущность отредактирована. Изменены поля: ' . implode(', ', $changedField));
        }

        return $message;
    }
}