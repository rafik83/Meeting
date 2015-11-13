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
use Proximum\Vimeet\Domain\Model\SheetView;
use Proximum\Vimeet\Domain\Model\User;

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
     * @param int|User  $user
     * @param int|Event $event
     * @param string    $locale
     *
     * @return SheetView[]
     */
    public function getSheetViewsByUserAndEvent($user, $event, $locale);

    /**
     * @param int $sheetId
     *
     * @return Sheet
     */
    public function getSheetById($sheetId);

    /**
     * @param Category|int $category
     * @param User|int     $user
     *
     * @return Sheet[]
     */
    public function search($category, $user);
}
