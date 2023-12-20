<?php

namespace Vich\UploaderBundle\EventListener\Doctrine;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * UploadListener.
 *
 * Handles file uploads.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class UploadListener extends BaseListener
{
    /**
     * @param LifecycleEventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function onFlush(OnFlushEventArgs $event): void
    {
        $uow = $event->getObjectManager()->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$this->isUploadable($entity)) { continue; }

            foreach ($this->getUploadableFields($entity) as $field) {
                $this->handler->upload($entity, $field);
                $uow->recomputeSingleEntityChangeSet($event->getObjectManager()->getClassMetadata($entity::class), $entity);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $event The event
     *
     * @throws \Vich\UploaderBundle\Exception\MappingNotFoundException
     */
    public function preUpdate(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();

        if (!$this->isUploadable($object)) {
            return;
        }

        foreach ($this->getUploadableFields($object) as $field) {
            $this->handler->upload($object, $field);
        }

        $this->adapter->recomputeChangeSet($event);
    }
}
