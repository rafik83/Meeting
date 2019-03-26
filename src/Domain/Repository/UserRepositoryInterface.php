<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\Query\Rooming\RoomingList\View\UserSheetTypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;

interface UserRepositoryInterface
{
    /**
     * @param $email
     *
     * @return bool
     */
    public function emailExists($email);

    /**
     * @param User $user
     */
    public function add(User $user);

    /**
     * @param User $user
     */
    public function set(User $user);

    /**
     * @param string $email
     *
     * @return null|User
     */
    public function findByEmail($email): ?User;

    public function findOneById(int $id): ?User;

    public function findByIds(array $ids): array;

    /**
     * @return User[]
     */
    public function all();

    /**
     * @param int    $page
     * @param int    $limit
     * @param Event  $event
     * @param array  $filter
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function paginate($page, $limit, Event $event, array $filter, $locale);

    /**
     * @param int[] $ids
     *
     * @return User[]
     */
    public function getByIdsIndexedById(array $ids);

    /**
     * @param Sheet[] $sheets
     *
     * @return User[]
     */
    public function getUsersParticipantOfSheets(array $sheets);

    /**
     * @param Event $event
     *
     * @return User[]
     *
     * @deprecated use findWithEnabledSheetByEvent()
     */
    public function findByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return User[]
     */
    public function findWithEnabledSheetByEvent(Event $event): array;

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return UserSheetTypeView[]
     */
    public function getWithSheetAndTypeByEvent(Event $event, string $locale): array;

    /**
     * @param Event $event
     *
     * @return User[]
     */
    public function findWithSheetByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return User[]
     */
    public function findByEventAndInCatalog(Event $event);

    /**
     * @param Event $event
     * @param Mass  $mass with dispatch=true
     *
     * @return User[]
     */
    public function findByEventWithoutDispatch(Event $event, Mass $mass);

    public function findByAuthenticationTokenAndEvent(string $token, Event $event, \DateTimeInterface $expiredAt): ?User;

    /**
     * @param Product $product
     *
     * @return User[]
     */
    public function findByParticipantProduct(Product $product): array;
}
