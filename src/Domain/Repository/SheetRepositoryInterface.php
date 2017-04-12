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
use Proximum\Vimeet\Domain\Model\EventInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\SheetView;

interface SheetRepositoryInterface
{
    /**
     * @param Sheet $sheet
     */
    public function add(Sheet $sheet);

    /**
     * @param Sheet $sheet
     */
    public function set(Sheet $sheet);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getSheetsInCatalogByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getSheetsInCatalogWithAtLeastOneAcceptedRequestByEvent(Event $event);

    /**
     * @param Type $type
     *
     * @return bool
     */
    public function isThereAtLeastOneByType(Type $type);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet[]
     */
    public function getSheets(Event $event, $locale);

    /**
     * @param int|User  $user
     * @param int|Event $event
     * @param string    $locale
     *
     * @return SheetView[]
     */
    public function getSheetViewsByUserAndEvent($user, $event, $locale);

    /**
     * Get only enabled sheet by user or user's participant
     *
     * @param User  $user
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getSheetsByUserAndEvent(User $user, Event $event);

    /**
     * Get all sheets whatever its enabled state by user or user's participant
     *
     * @param User  $user
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getAllSheetsByUserAndEvent(User $user, Event $event);

    /**
     * @param User           $user
     * @param EventInterface $event
     *
     * @return Sheet[]
     */
    public function getSheetsByUserAndEventWhereUserIsParticipant(User $user, EventInterface $event);

    /**
     * @param int $sheetId
     *
     * @return null|Sheet
     */
    public function getSheetById($sheetId);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsById(array $ids);

    /**
     * @param Event $event
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsByEventAndIds(Event $event, array $ids);

    /**
     * @param Event   $event
     * @param Sheet[] $sheets
     *
     * @return Sheet[]
     */
    public function getSheetsMetBySheets(Event $event, array $sheets);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getUnvalidatedSheetsById(array $ids);
  
    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsUnacceptedById(array $ids);

    /**
     * @param User  $user
     * @param array $types
     *
     * @return Sheet[]
     */
    public function getUserSheetsByTypes(User $user, array $types);

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getIdsByEvent(Event $event);

    /**
     * @param array $sheets
     *
     * @return Sheet[]
     */
    public function findSheets(array $sheets);

    /**
     * @param array $sheetIds
     *
     * @return Sheet[]
     */
    public function findByIds(array $sheetIds);

    /**
     * @param array $sheets
     *
     * @return Sheet[]
     */
    public function findFullSheets(array $sheets);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Sheet[]
     */
    public function getEnabledSheetsByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return int
     */
    public function countEnabledSheetsByEvent(Event $event);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return array
     */
    public function countEnabledSheetsTypeByEvent(Event $event, $locale);

    /**
     * @param User $user
     *
     * @return Sheet[]
     */
    public function getByUser(User $user);

    /**
     * @param Group $group
     *
     * @return Sheet[]
     */
    public function getByGroup(Group $group);
}
