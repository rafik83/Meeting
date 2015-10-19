<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\Participant;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Participant\TypeView;
use Proximum\Vimeet\Domain\Repository\Participant\TypeRepositoryInterface;

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
     * @param integer $eventId
     * @param string  $locale
     *
     * @return TypeView[]
     */
    public function getTypeViewsByEvent($eventId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\Participant\TypeView(type.id, translations.title)')
            ->from('Entity:Participant\Type', 'type')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('type.position');

        return $queryBuilder->getQuery()->getResult();
    }
}
