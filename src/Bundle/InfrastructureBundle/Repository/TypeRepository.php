<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeRepository implements TypeRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewById($typeId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\TypeView(type.id, translations.title)')
            ->from('Entity:Type', 'type')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewsByEvent($eventId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\TypeView(type.id, translations.title)')
            ->from('Entity:Type', 'type')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('type.position');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeTemplatesViewById($typeId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\TypeTemplatesView(type.id, type.participantTemplate, type.sheetTemplate, type.packageTemplate)')
            ->from('Entity:Type', 'type')
            ->where('type.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from('Entity:Type', 'type')
            ->where('type.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantTemplate($typeId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('PARTIAL type.{id,participantTemplate}')
            ->from('Entity:Type', 'type')
            ->where('type.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->setMaxResults(1);

        $type = $queryBuilder->getQuery()->getOneOrNullResult();

        return $type ? $type->getParticipantTemplate() : [];
    }
}
