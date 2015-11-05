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
    public function getSheetsIdByUserAndEvent($user, $event, $locale);

    /**
     * @param array $filters
     *
     * @return Sheet[]
     */
    public function search(array $filters);
}
