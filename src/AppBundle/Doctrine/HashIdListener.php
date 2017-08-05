<?php

namespace AppBundle\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Hashids\Hashids;

class HashIdListener
{

    /**
     * @var Hashids
     */
    private $hashIdService;

    public function __construct(Hashids $hashIdService)
    {
        $this->hashIdService = $hashIdService;
    }


    /**
     * Get post event of doctrine and add in property hashid current id of entity movies
     * The postLoad event occurs for an entity after the entity has been loaded into the current
     * EntityManager from the database or after the refresh operation has been applied to it.
     *
     * @param LifecycleEventArgs $args Lifecycle of doctrine
     */
    public function postLoad(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();
        $reflectionClass = new \ReflectionClass($entity);

        if (!$reflectionClass->hasProperty('hashId')) {
            return;
        }
        $hashId = $this->hashIdService->encode($entity->getId());


        $property = $reflectionClass->getProperty('hashId');
        $property->setAccessible(true);
        $property->setValue($entity, $hashId);

    }


}