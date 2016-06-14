<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @param ManagerRegistry $manager
     */
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function set(Content $content)
    {
        $this->manager->getManager()->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getTermsOfSalesByEvent(Event $event)
    {
        $queryBuilder = $this
            ->manager
            ->getManager()
            ->createQueryBuilder()
            ->select('content')
            ->from(Content::class, 'content')
            ->where('content.event = :event')
            ->setParameter('event', $event)
            ->andWhere('content.type = :type')
            ->setParameter('type', Content::TYPE_TERMS_OF_SALE)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
