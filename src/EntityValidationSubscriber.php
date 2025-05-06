<?php

namespace EnsoStudio\Doctrine\ORM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Entity event subscriber to validates column values before persist/update entity.
 *
 * ```php
 * $entityManager->getEventManager()->addEventSubscriber(
 *     new \EnsoStudio\Doctrine\ORM\EntityValidationSubscriber(true)
 * );
 * ```
 */
class EntityValidationSubscriber implements EventSubscriber
{
    /**
     * @param bool $useValidatorCache Sets {@see EntityValidator::$useCache}
     */
    public function __construct(
        public readonly bool $useValidatorCache = false
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist, Events::preUpdate];
    }

    /**
     * @throws EntityValidationException If validation failed
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $validator = new EntityValidator($args->getObject(), $this->useValidatorCache);
        $validator->validate();
    }

    /**
     * @throws EntityValidationException If validation failed
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $validator = new EntityValidator($args->getObject(), $this->useValidatorCache);
        $validator->validate(true);
    }
}