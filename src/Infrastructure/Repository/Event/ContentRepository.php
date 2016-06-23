<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository\Event;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Domain\Repository\Event\ContentRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ContentRepository implements ContentRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->manager = $registry->getManager();
    }

    /**
     * {@inheritdoc}
     */
    public function set(Content $content)
    {
        $this->manager->flush($content);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndType(Event $event, $type)
    {
        $queryBuilder = $this
            ->manager
            ->createQueryBuilder()
            ->select('content')
            ->from(Content::class, 'content')
            ->where('content.event = :event')
            ->setParameter('event', $event)
            ->andWhere('content.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
