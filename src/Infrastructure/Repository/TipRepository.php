<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipRepository implements TipRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;
    
    /** @var Paginator */
    private $paginator;
    
    /**
     * SpotUnavailabilityRepository constructor.
     *
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }
    
    /** {@inheritdoc} */
    public function paginate($page, $limit = 20)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip', 'tip.id')
            ->orderBy('tip.title');
        
        return $this->paginator->paginate($queryBuilder, $page, $limit, 'tip');
    }
    
    /** {@inheritdoc} */
    public function add(Tip $tip)
    {
        $this->entityManager->persist($tip);

        foreach ($tip->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /** {@inheritdoc} */
    public function set(Tip $tip)
    {
        $this->entityManager->flush($tip);

        foreach ($tip->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /** {@inheritdoc} */
    public function removeTranslation(TipTranslation $translation)
    {
        $this->entityManager->remove($translation);
    }
}
