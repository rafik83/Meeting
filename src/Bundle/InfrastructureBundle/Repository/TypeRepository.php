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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
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

    /**
     * {@inheritdoc}
     */
    public function getTypesByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from('Entity:Type', 'type')
            ->where('type.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeeableTypeIdsByUser($user)
    {
        return $this->seeableTypeBySees($this->seesBySheets($this->sheetByUser($user)));
    }

    /**
     * @param User|int $user
     *
     * @return array
     */
    private function sheetByUser($user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet.id')
            ->from('Entity:Sheet', 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user);

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param array $sheets
     *
     * @return array
     */
    private function seesBySheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('see.id')
            ->from('Entity:See', 'see', 'see.id')
            ->leftJoin('see.seerCategory', 'seerCategory')
            ->leftJoin('seerCategory.types', 'seerCategoryType')
            ->leftJoin('see.seerType', 'seerType')
            ->join('Entity:Sheet', 'sheet', 'WITH', '(sheet.type = seerCategoryType OR sheet.type = seerType) AND sheet IN (:sheets)')
            ->setParameter('sheets', $sheets);

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param array $sees
     *
     * @return array
     */
    private function seeableTypeBySees(array $sees)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('DISTINCT seeableType.id')
            ->from('Entity:Type', 'seeableType', 'seeableType.id')
            ->leftJoin('seeableType.categories', 'seeableCategory')
            ->join('Entity:See', 'see', 'WITH', '(see.seeableType = seeableType OR see.seeableCategory = seeableCategory) AND see IN (:sees)')
            ->setParameter('sees', $sees);

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
