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
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * @param EntityManager              $entityManager
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(EntityManager $entityManager, RequestRepositoryInterface $requestRepository)
    {
        $this->entityManager = $entityManager;
        $this->requestRepository = $requestRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('rule')
            ->from('Entity:Rule', 'rule')
            ->where('rule.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventSeerAndSeeable(Event $event, WhoInterface $seer, WhoInterface $seeable)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('rule')
            ->from('Entity:Rule', 'rule')
            ->where('rule.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        if ($seer instanceof Type) {
            $queryBuilder
                ->andWhere('rule.seerType = :seerType')
                ->setParameter('seerType', $seer);
        } elseif ($seer instanceof Category) {
            $queryBuilder
                ->andWhere('rule.seerCategory = :seerCategory')
                ->setParameter('seerCategory', $seer);
        }

        if ($seeable instanceof Type) {
            $queryBuilder
                ->andWhere('rule.seeableType = :seeableType')
                ->setParameter('seeableType', $seeable);
        } elseif ($seeable instanceof Category) {
            $queryBuilder
                ->andWhere('rule.seeableCategory = :seeableCategory')
                ->setParameter('seeableCategory', $seeable);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getByEventAndSeer(Event $event, WhoInterface $seer): ?Rule
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('rule')
            ->from('Entity:Rule', 'rule')
            ->where('rule.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        if ($seer instanceof Type) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    'rule.seerType = :seerType',
                    'rule.seerCategory IN (:seerCategories)'
                ))
                ->setParameter('seerCategories', $seer->getCategories())
                ->setParameter('seerType', $seer);
        } elseif ($seer instanceof Category) {
            $queryBuilder
                ->andWhere('rule.seerCategory = :seerCategory')
                ->setParameter('seerCategory', $seer);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySeerTypeAndSeeableType(Type $seer, Type $seeable)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder();

        $queryBuilder
            ->select('rule')
            ->from(Rule::class, 'rule')
            ->where($queryBuilder->expr()->orX(
                'rule.seerType = :seerType',
                'rule.seerCategory IN (:seerCategories)'
            ))
            ->setParameter('seerType', $seer)
            ->setParameter('seerCategories', $seer->getCategories())
            ->andWhere($queryBuilder->expr()->orX(
                'rule.seeableType = :seeableType',
                'rule.seeableCategory IN (:seeableCategories)'
            ))
            ->setParameter('seeableType', $seeable)
            ->setParameter('seeableCategories', $seeable->getCategories());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getBySeerSheetAndSeeableSheet(Sheet $seerSheet, Sheet $seeableSheet): array
    {
        $rules = $this->getBySeerTypeAndSeeableType($seerSheet->getType(), $seeableSheet->getType());

        if (!empty($rules)) {
            return $rules;
        }

        if (false === $this->requestRepository->hasRequestBetweenSheets($seerSheet, $seeableSheet)) {
            return [];
        }

        // if there's at least one request between sheets, the default rule is used
        $defaultRule = Rule::createDefault(
            $seeableSheet->getEvent(),
            $seerSheet->getType(),
            $seeableSheet->getType()
        );

        return [$defaultRule];
    }

    /**
     * {@inheritdoc}
     */
    public function add(Rule $rule)
    {
        $this->entityManager->persist($rule);
        $this->entityManager->flush($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function set(Rule $rule)
    {
        $this->entityManager->flush($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Rule $rule)
    {
        dump($rule);
        $this->entityManager->flush($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Rule $rule)
    {
        $this->entityManager->remove($rule);
        $this->entityManager->flush($rule);
    }
}
