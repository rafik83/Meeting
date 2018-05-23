<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\View\EventListView;

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
     * @param Admin $admin
     *
     * @return Event[]
     */
    public function findArchivedByAdmin(Admin $admin): array;

    /**
     * Get events order by title and last event day
     *
     * @param Admin $admin
     *
     * @return Event[]
     */
    public function getEventsWithDaysByAdmin(Admin $admin);

    /**
     * @return Event[]
     */
    public function getEventsOrderByIdDesc(): array;

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
    public function add(Event $event);

    /**
     * @param Event $event
     */
    public function set(Event $event);

    /**
     * @param string $domain
     *
     * @return null|Event
     */
    public function getEventByDomain($domain);

    /**
     * @param int $id
     *
     * @return Event|null
     */
    public function getById($id);

    /**
     * @param string[] $parameters array of Event Extra Parameter type
     *
     * @return Event[]
     */
    public function findEventWithParameters(array $parameters): array;

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return Event[]
     */
    public function findByDay(\DateTimeInterface $dateTime): array;

    public function findFutureEventsOrEventsWithoutDaysByAdmin(Admin $admin, Event $excludedEvent): iterable;
}
