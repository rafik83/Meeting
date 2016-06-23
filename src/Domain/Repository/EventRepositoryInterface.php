<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\EventListView;
use Proximum\Vimeet\Domain\View\EventView;

interface EventRepositoryInterface
{
    /**
     * @param Admin $admin
     *
     * @return EventListView[]
     */
    public function getListByAdmin(Admin $admin);

    /**
     * @param Admin $admin
     *
     * @return Event[]
     */
    public function getEventsByAdmin(Admin $admin);

    /**
     * @return EventListView[]
     */
    public function getList();

    /**
     * @return Event[]
     */
    public function getAll();

    /**
     * Save event
     *
     * @param Event $event
     */
    public function set(Event $event);

    /**
     * @param string $domain
     *
     * @return Event
     */
    public function getEventByDomain($domain);

    /**
     * @param string $domain
     * @param string $locale
     *
     * @return EventView
     */
    public function getEventViewByDomain($domain, $locale);

    /**
     * @param int $id
     *
     * @return Event
     */
    public function getById($id);
}
