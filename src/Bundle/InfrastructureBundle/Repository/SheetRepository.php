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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\NomenclatureItemRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetRepository implements SheetRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var NomenclatureItemRepositoryInterface
     */
    private $nomenclatureItemRepository;

    /**
     * @param EntityManager $entityManager
     * @param NomenclatureItemRepositoryInterface $nomenclatureItemRepository
     */
    public function __construct(
        EntityManager $entityManager,
        NomenclatureItemRepositoryInterface $nomenclatureItemRepository
    ) {
        $this->entityManager              = $entityManager;
        $this->nomenclatureItemRepository = $nomenclatureItemRepository;
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
    public function getSheetById($sheetId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from('Entity:Sheet', 'sheet')
            ->where('sheet.id = :sheetId')
            ->setParameter('sheetId', $sheetId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function search(array $filters)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sheet')
            ->from('Entity:Sheet', 'sheet');

        if (isset($filters['type'])) {
            $queryBuilder
                ->andWhere('sheet.type IN (:type)')
                ->setParameter('type', $filters['type']);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getData(Sheet $sheet, $locale)
    {
        $sheetTemplate = $sheet->getTypeSheetTemplate();
        $data          = $sheet->getData();

        foreach ($sheetTemplate as $keyBlock => $block) {
            if (isset($block['template'])) {
                foreach ($block['template'] as $keyField => $field) {
                    if (isset($field['type'])
                        && 'lib_nomenclature' === $field['type']
                        && isset($data[$keyBlock][$keyField]['value'])
                    ) {
                        $data[$keyBlock][$keyField]['label'] = $this
                            ->nomenclatureItemRepository
                            ->getNomenclatureItemLabelById($data[$keyBlock][$keyField]['value'], $locale);
                    }
                }
            }
        }

        return $data;
    }
}
