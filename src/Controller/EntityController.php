<?php

namespace App\Controller;

use Doctrine\ORM\EntityRepository;

abstract class EntityController extends BaseController
{
    protected function getEntityRepository(): EntityRepository
    {
        if (!empty($this->entityRepository)) {
            return $this->entityRepository;
        }
        $this->entityRepository = $this->entityManager->getRepository($this->getControllerEntityClass());
        return $this->entityRepository;
    }

    protected abstract function getControllerEntityClass(): string;
}