<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\View\TypeView;

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
            ->select('type')
            ->from(Type::class, 'type', 'type.id')
            ->join('type.translations', 'translation', 'WITH', 'type.event = :eventId')
            ->setParameter('eventId', $eventId)
            ->orderBy('type.position', 'ASC');

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
    public function countByEvent(Event $event): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(type.id)')
            ->from(Type::class, 'type', 'type.id')
            ->where('type.event = :event')
            ->setParameter('event', $event);

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewById($typeId, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf('NEW %s(type.id, translations.title, translations.description, type.hidden)', TypeView::class)
            )
            ->from(Type::class, 'type', 'type.id')
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
    public function getTypeViewsByIds(array $typeIds, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf('NEW %s(type.id, translations.title, translations.description, type.hidden)', TypeView::class)
            )
            ->from(Type::class, 'type', 'type.id')
            ->join('type.translations', 'translations', 'WITH', 'type.id IN (:typeIds) AND translations.locale = :locale')
            ->setParameter('typeIds', $typeIds)
            ->setParameter('locale', $locale);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewByIdAndEvent($typeId, Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf('NEW %s(type.id, translations.title, translations.description, type.hidden)', TypeView::class)
            )
            ->from(Type::class, 'type', 'type.id')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.id = :typeId')
            ->setParameter('typeId', $typeId)
            ->andWhere('type.event = :event')
            ->setParameter('event', $event)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleTypesViewsByEvent(Event $event, $locale): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf('NEW %s(type.id, translations.title, translations.description, type.hidden)', TypeView::class)
            )
            ->from(Type::class, 'type')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.event = :event')
            ->andWhere('type.hidden = false')
            ->setParameter('event', $event)
            ->orderBy('type.position');

        return $queryBuilder->getQuery()->getResult();
    }

    public function hasVisibleTypeByEvent(Event $event): bool
    {
        return null !== $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type.id')
            ->from(Type::class, 'type')
            ->where('type.event = :event AND type.hidden = false')
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeViewsByEvent(Event $event, $locale, Type $excludedType = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select(
                sprintf('NEW %s(type.id, translations.title, translations.description, type.hidden)', TypeView::class)
            )
            ->from(Type::class, 'type')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.event = :event')
            ->setParameter('event', $event)
            ->orderBy('type.position');

        if (null !== $excludedType) {
            $queryBuilder
                ->andWhere('type != :excludedType')
                ->setParameter('excludedType', $excludedType);
        }

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
    public function getById($id): ?Type
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
    public function getByIds(array $ids)
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from(Type::class, 'type')
            ->where('type.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
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
    public function getLocalizedTypesByEvent(Event $event, $locale)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type, translation')
            ->from(Type::class, 'type', 'type.id')
            ->join('type.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->where('type.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedTypesByEvent(Admin $admin, Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from(Type::class, 'type', 'type.id')
            ->join('type.admins', 'admins', 'WITH', 'admins.id = :admin')
            ->where('type.event = :event')
            ->setParameter('admin', $admin)
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getAllowedTypesExcludedCurrentEventByAdmin(Admin $admin, Event $excludedEvent, \DateTimeInterface $datetime): iterable
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type, typeTranslation')
            ->from(Type::class, 'type', 'type.id')
            ->join('type.translations', 'typeTranslation')
            ->join('type.event', 'event', 'WITH', 'event != :excludedEvent')
            ->leftJoin('event.days', 'days')
            ->where('days.id IS NULL OR days.startTime > :datetime')
            ->setParameters([
                'excludedEvent' => $excludedEvent,
                'datetime' => $datetime,
            ])
            ->orderBy('event.title, typeTranslation.title', 'ASC');

        if ($admin->hasEvents()) {
            $queryBuilder
                ->andWhere('event IN (:events)')
                ->setParameter('events', $admin->getEvents());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesTitleByEventAndLocale(Event $event, $locale, array $types = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type.id, translations.title')
            ->from('Entity:Type', 'type', 'type.id')
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->where('type.event = :event')
            ->setParameter('event', $event)
            ->setParameter('locale', $locale)
            ->orderBy('type.position');

        if (null !== $types) {
            $queryBuilder->andWhere('type IN (:types)')->setParameter('types', $types);
        }

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
    public function getFirstPositionTypeByEventAndUser(Event $event, User $user)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from(Type::class, 'type')
            ->where('type.event = :event')
            ->setParameter('event', $event)
            ->join(Sheet::class, 'sheet', 'WITH', 'sheet.type = type')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user = :user')
            ->setParameter('user', $user)
            ->orderBy('type.position')
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSeeableTypeIdsByUser(User $user)
    {
        return $this->seeableTypeByRules($this->rulesBySheets($this->sheetByUser($user)));
    }

    /**
     * @param User|int $user
     *
     * @return array
     */
    private function sheetByUser(User $user)
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
    public function getTypesByUserIds(Event $event, array $userIds): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from(Type::class, 'type')
            ->join(Sheet::class, 'sheet', 'WITH', 'type.event = :event AND sheet.type = type AND sheet.enable = true')
            ->join('sheet.participants', 'participant', 'WITH', 'participant.user IN (:userIds)')
            ->setParameter('userIds', $userIds)
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function typeExists(Event $event, $locale, $title, $excludedType = null)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type.id')
            ->from('Entity:Type', 'type')
            ->join(
                'type.translations',
                'translations',
                'WITH',
                sprintf(
                    'type.event = :event AND translations.locale = :locale AND translations.title = :title %s',
                    null !== $excludedType ? 'AND type.id != :type' : ''
                )
            )
            ->setParameter('event', $event)
            ->setParameter('title', $title)
            ->setParameter('locale', $locale);

        if (null !== $excludedType) {
            $queryBuilder->setParameter('type', $excludedType);
        }

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult() ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Type $type)
    {
        $this->entityManager->remove($type);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypesWithPaymentConditionsByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type')
            ->from(Type::class, 'type', 'type.id')
            ->join('type.paymentConditions', 'paymentConditions', 'WITH', 'type.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFromSheetMeetingRequests(Sheet $sheet, string $locale): array
    {
        // retrieve
        $query = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type', 'translations')
            ->from('Entity:Type', 'type')
            ->where(
                'type in (
                        SELECT tmpType.id
                        FROM Entity:Meeting\Request meetingRequest
                            INNER JOIN meetingRequest.from sheet WITH sheet = :sheet AND meetingRequest.disabled = false
                            INNER JOIN meetingRequest.to toSheet
                            INNER JOIN toSheet.type tmpType
            )'
            )
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('sheet', $sheet)
            ->setParameter('locale', $locale)
            ->getQuery();

        $types = $query->getResult();

        $query = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('type', 'translations')
            ->from('Entity:Type', 'type')
            ->where(
                'type in (
                        SELECT tmpType.id
                        FROM Entity:Meeting\Request meetingRequest
                            INNER JOIN meetingRequest.to sheet WITH sheet = :sheet AND meetingRequest.disabled = false
                            INNER JOIN meetingRequest.from fromSheet
                            INNER JOIN fromSheet.type tmpType
                )'
            )
            ->join('type.translations', 'translations', 'WITH', 'translations.locale = :locale')
            ->setParameter('sheet', $sheet)
            ->setParameter('locale', $locale)
            ->getQuery();

        $otherSideTypes = $query->getResult();

        // merge
        foreach ($otherSideTypes as $category) {
            if (false === in_array($category, $types, true)) {
                $types[] = $category;
            }
        }

        // sort
        usort(
            $types,
            function (Type $typeA, Type $typeB) use ($locale) {
                return $typeA->getTitle($locale) <=> $typeB->getTitle($locale);
            }
        );

        return $types;
    }

    public function getTypesAndCategoriesTranslationsByEvent(Event $event, string $locale): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('type, typeTranslation, category, categoryTranslation')
            ->from(Type::class, 'type', 'type.id')
            ->join(
                'type.translations',
                'typeTranslation',
                'WITH',
                'type.event = :event AND typeTranslation.locale = :locale'
            )
            ->leftJoin('type.categories', 'category')
            ->leftJoin(
                'category.translations',
                'categoryTranslation',
                'WITH',
                'categoryTranslation.locale = :locale'
            )
            ->setParameter('event', $event)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }
}
