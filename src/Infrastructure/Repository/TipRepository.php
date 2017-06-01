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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Model\Type;
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
    public function getByTipTranslationId($id)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip')
            ->from(Tip::class, 'tip')
            ->join(TipTranslation::class, 'tipTranslation', 'WITH', 'tip = tipTranslation.tip')
            ->where('tipTranslation = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
        foreach ($tip->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /** {@inheritdoc} */
    public function setTypes(Tip $tip)
    {
        $this->entityManager->flush($tip);

        foreach ($tip->getTypes() as $type) {
            $this->entityManager->flush($type);
        }
    }

    /** {@inheritdoc} */
    public function removeTranslation(TipTranslation $translation)
    {
        $this->entityManager->remove($translation);
    }

    /** {@inheritdoc} */
    public function removeTip(Tip $tip)
    {
        foreach ($tip->getTypes() as $type) {
            $tip->removeType($type);
        }

        $this->entityManager->flush($tip);
    }

    /** {@inheritdoc} */
    public function getByContextAndEventAndType(Event $event, Type $type, $context, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('new \Proximum\Vimeet\Application\View\Tip\TipTranslationView(tipTranslation.id, tipTranslation.title, tipTranslation.content, tip.title)')
            ->from(Tip::class, 'tip')
            ->join('tip.translations', 'tipTranslation', 'WITH', sprintf('tip.%s = true AND tipTranslation.locale = :locale', $context))
            ->join('tip.types', 'type', 'WITH', 'type.event = :event and type = :type')
            ->orderBy('tip.createdAt')
            ->groupBy('type.event, tip.id')
            ->setParameter('locale', $locale)
            ->setParameter('event', $event)
            ->setParameter('type', $type);

        return $queryBuilder->getQuery()->getResult();
    }

    /** {@inheritdoc} */
    public function getByEventAndTip(Event $event, Tip $tip)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, type')
            ->from(Tip::class, 'tip')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event AND tip = :tip')
            ->setParameter('event', $event)
            ->setParameter('tip', $tip);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /** {@inheritdoc} */
    public function paginateByEvent(Event $event, $page, $limit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('tip, type')
            ->from(Tip::class, 'tip', 'tip.id')
            ->join('tip.types', 'type', 'WITH', 'type.event = :event')
            ->setParameter('event', $event);

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'tip');
    }

    /** {@inheritdoc} */
    public function getTipTranslationViewByLocale($locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('new \Proximum\Vimeet\Application\View\Tip\TipTranslationView(tipTranslation.id, tipTranslation.title, tipTranslation.content, tip.title)')
            ->from(Tip::class, 'tip')
            ->join('tip.translations', 'tipTranslation', 'WITH', 'tipTranslation.locale = :locale')
            ->orderBy('tip.title')
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }
}
