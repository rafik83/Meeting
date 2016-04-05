<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\SheetView;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

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
     * @param Event  $event
     * @param string $locale
     * @param int    $page
     * @param int    $limit
     *
     * @return array
     */
    public function getSheetsMeetingsStats(Event $event, $locale, $page, $limit);

    /**
     * @param int|User  $user
     * @param int|Event $event
     * @param string    $locale
     *
     * @return SheetView[]
     */
    public function getSheetViewsByUserAndEvent($user, $event, $locale);

    /**
     * @param User   $user
     * @param Event  $event
     *
     * @return Sheet[]
     */
    public function getSheetByUserAndEvent(User $user, Event $event);

    /**
     * @param int $sheetId
     *
     * @return Sheet
     */
    public function getSheetById($sheetId);

    /**
     * @param array $ids
     *
     * @return Sheet[]
     */
    public function getSheetsById(array $ids);

    /**
     * @param Category|int $category
     * @param User|int     $user
     *
     * @return Sheet[]
     */
    public function search($category, $user);

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
}
