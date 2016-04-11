<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class TypeRepository implements TypeRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**s
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator     = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($page, $limit, $eventId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\TypeListView(type.id, translation.title)')
            ->from('Entity:Type', 'type', 'type.id')
            ->join('type.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->join('type.event', 'event', 'WITH', 'event.id = :eventId')
            ->setParameter('locale', $locale)
            ->setParameter('eventId', $eventId)
            ->orderBy('type.id', 'ASC');

        return $this->paginator->paginate($queryBuilder, $page, $limit, 'type', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function add(Type $type)
    {
        $this->entityManager->persist($type);
        $this->entityManager->flush($type);

        foreach ($type->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Type $type)
    {
        $this->entityManager->flush($type);

        foreach ($type->getTranslations() as $translation) {
            $this->entityManager->flush($translation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewById($typeId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('NEW Proximum\Vimeet\Domain\View\TypeView(type.id, translations.title)')
            ->from('Entity:Type', 'type', 'type.id')
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
            ->select('NEW Proximum\Vimeet\Domain\View\TypeView(type.id, translations.title)')
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
            ->select('NEW Proximum\Vimeet\Domain\View\TypeTemplatesView(type.id, type.participantTemplate, type.sheetTemplate, type.packageTemplate)')
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
            ->from('Entity:Type', 'type', 'type.id')
            ->where('type.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesTitleByEventAndLocale(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type.id, translations.title')
            ->from('Entity:Type', 'type', 'type.id')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->where('type.event = :event')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale);

        return array_map(
            function ($type) {
                return $type['title'];
            },
            $queryBuilder->getQuery()->getResult()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSeeableTypeIdsByUser($user)
    {
        return $this->seeableTypeByRules($this->rulesBySheets($this->sheetByUser($user)));
    }

    /**
     * {@inheritdoc}
     */
    public function getSeeableTypeIdsBySheet(Sheet $sheet)
    {
        return $this->seeableTypeByRules($this->rulesBySheets([$sheet]));
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
    private function rulesBySheets(array $sheets)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('rule.id')
            ->from('Entity:Rule', 'rule', 'rule.id')
            ->leftJoin('rule.seerCategory', 'seerCategory')
            ->leftJoin('seerCategory.types', 'seerCategoryType')
            ->leftJoin('rule.seerType', 'seerType')
            ->join('Entity:Sheet', 'sheet', 'WITH', '(sheet.type = seerCategoryType OR sheet.type = seerType) AND sheet IN (:sheets)')
            ->setParameter('sheets', $sheets);

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    private function seeableTypeByRules(array $rules)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('DISTINCT seeableType.id')
            ->from('Entity:Type', 'seeableType', 'seeableType.id')
            ->leftJoin('seeableType.categories', 'seeableCategory')
            ->join('Entity:Rule', 'rule', 'WITH', '(rule.seeableType = seeableType OR rule.seeableCategory = seeableCategory) AND rule IN (:rules)')
            ->setParameter('rules', $rules);

        return array_keys($queryBuilder->getQuery()->getResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesByUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from('Entity:Type', 'type')
            ->join('Entity:Sheet', 'sheet', 'WITH', 'sheet.type = type')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->where('type.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTypesByUser(User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from('Entity:Type', 'type')
            ->join('Entity:Sheet', 'sheet', 'WITH', 'sheet.type = type')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }
}
