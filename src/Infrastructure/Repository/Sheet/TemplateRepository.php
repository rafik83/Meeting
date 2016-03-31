<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SpotRepository constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('template')
            ->from(Template::class, 'template');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(Template $template)
    {
        $this->entityManager->persist($template);
        $this->entityManager->flush($template);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Template $template)
    {
        $this->entityManager->flush($template);
    }
}
