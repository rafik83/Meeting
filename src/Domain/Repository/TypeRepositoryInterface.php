<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\TypeListView;
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
     * @return TypeListView[]
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
     * @param int    $eventId
     * @param string $locale
     *
     * @return TypeView[]
     */
    public function getTypeViewsByEvent($eventId, $locale);

    /**
     * @param int    $typeId
     * @param string $locale
     *
     * @return TypeView
     */
    public function getTypeViewById($typeId, $locale);

    /**
     * @param int $typeId
     *
     * @return TypeTemplatesView
     */
    public function getTypeTemplatesViewById($typeId);

    /**
     * @param int $id
     *
     * @return Type
     */
    public function getById($id);

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
     * @return Type[] indexed by Type::id
     */
    public function getTypesTitleByEventAndLocale(Event $event, $locale);

    /**
     * @param User|int $user
     *
     * @return array
     */
    public function getSeeableTypeIdsByUser($user);

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    public function getSeeableTypeIdsBySheet(Sheet $sheet);

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Type[]
     */
    public function getTypesByUser(Event $event, User $user);

    /**
     * @param User  $user
     *
     * @return Type[]
     */
    public function getAllTypesByUser(User $user);
}
