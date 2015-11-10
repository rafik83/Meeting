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
use Proximum\Vimeet\Bundle\InfrastructureBundle\Doctrine\ORM\QueryBuilder\Sheet\SearchQueryBuilder;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetRepository implements SheetRepositoryInterface
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
    public function add(Sheet $sheet)
    {
        $this->entityManager->persist($sheet);
        $this->entityManager->flush($sheet);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Sheet $sheet)
    {
        $this->entityManager->flush($sheet);
    }

    /**
     * {@inheritdoc}
     */
    public function getSheetsIdByUserAndEvent($user, $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\Model\SheetView(sheet.id, typeTranslation.title)')
            ->from('Entity:Sheet', 'sheet', 'sheet.id')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->join('sheet.type', 'type')
            ->join('type.translations', 'typeTranslation', 'WITH', 'typeTranslation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('sheet.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function search($category, $user)
    {
        $queryBuilder = new SearchQueryBuilder($this->entityManager);
        $queryBuilder->withCategory($category);
        $queryBuilder->withTypes($this->seeableTypeBySees($this->seesBySheets($this->sheetByUser($user))));

        return $queryBuilder->getQuery()->getResult();
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
            ->select('seeableType.id')
            ->from('Entity:Type', 'seeableType', 'seeableType.id')
            ->leftJoin('seeableType.categories', 'seeableCategory')
            ->join('Entity:See', 'see', 'WITH', '(see.seeableType = seeableType OR see.seeableCategory = seeableCategory) AND see IN (:sees)')
            ->setParameter('sees', $sees);

        return array_keys($queryBuilder->getQuery()->getResult());
    }
}
