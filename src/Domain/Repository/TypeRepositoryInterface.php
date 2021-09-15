<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\TypeTemplatesView;
use Proximum\Vimeet\Domain\View\TypeView;

interface TypeRepositoryInterface
{
    /**
     * @param int    $page
     * @param int    $limit
     * @param int    $eventId
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function paginate($page, $limit, $eventId, $locale);

    /**
     * @param Type $type
     */
    public function add(Type $type);

    /**
     * @param Type $type
     */
    public function set(Type $type);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEvent(Event $event): int;

    /**
     * @param Event     $event
     * @param string    $locale
     * @param null|Type $excludedType
     *
     * @return TypeView[]
     */
    public function getTypeViewsByEvent(Event $event, $locale, Type $excludedType = null);

    /**
     * @param int    $typeId
     * @param string $locale
     *
     * @return TypeView
     */
    public function getTypeViewById($typeId, $locale);

    /**
     * @param array  $typeIds
     * @param string $locale
     *
     * @return TypeView[]
     */
    public function getTypeViewsByIds(array $typeIds, $locale);

    /**
     * @param int    $typeId
     * @param Event  $event
     * @param string $locale
     *
     * @return TypeView
     */
    public function getTypeViewByIdAndEvent($typeId, Event $event, $locale);

    /**
     * @param int $typeId
     *
     * @return TypeTemplatesView
     */
    public function getTypeTemplatesViewById($typeId);

    /**
     * @param int $id
     *
     * @return null|Type
     */
    public function getById($id): ?Type;

    /**
     * @param int[] $ids
     *
     * @return Type[]
     */
    public function getByIds(array $ids);

    /**
     * @param $typeId
     *
     * @return array
     */
    public function getParticipantTemplate($typeId);

    /**
     * @param Event $event
     *
     * @return Type[]
     */
    public function getTypesByEvent(Event $event);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Type[]
     */
    public function getLocalizedTypesByEvent(Event $event, $locale);

    /**
     * @param Admin $admin
     * @param Event $event
     *
     * @return Type[]
     */
    public function getAllowedTypesByEvent(Admin $admin, Event $event);

    public function getAllowedTypesExcludedCurrentEventByAdmin(Admin $admin, Event $excludedEvent, \DateTimeInterface $dateTime): iterable;

    /**
     * @param Event      $event
     * @param string     $locale
     * @param array|null $types
     *
     * @return Type[] indexed by Type::id
     */
    public function getTypesTitleByEventAndLocale(Event $event, $locale, array $types = null);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Type|null
     */
    public function getFirstPositionTypeByEventAndUser(Event $event, User $user);

    /**
     * @param User|int $user
     *
     * @return array
     */
    public function getSeeableTypeIdsByUser(User $user);

    /**
     * @param int[] $userIds
     *
     * @return Type[]
     */
    public function getTypesByUserIds(Event $event, array $userIds): array;

    /**
     * @param Event     $event
     * @param string    $locale
     * @param string    $title
     * @param null|Type $excludedType
     *
     * @return bool
     */
    public function typeExists(Event $event, $locale, $title, $excludedType = null);

    /**
     * @param Type $type
     */
    public function remove(Type $type);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return TypeView[]
     */
    public function getVisibleTypesViewsByEvent(Event $event, $locale): array;

    public function hasVisibleTypeByEvent(Event $event): bool;

    /**
     * @param Event $event
     *
     * @return Type[]
     */
    public function getTypesWithPaymentConditionsByEvent(Event $event): array;

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return Type[]
     */
    public function getFromSheetMeetingRequests(Sheet $sheet, string $locale): array;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Type[]
     */
    public function getTypesAndCategoriesTranslationsByEvent(Event $event, string $locale): array;
}
