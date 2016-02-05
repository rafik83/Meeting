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
use Proximum\Vimeet\Domain\Model\Happening;

interface HappeningRepositoryInterface
{
    /**
     * @param Happening $happening
     */
    public function add(Happening $happening);

    /**
     * @param Happening $happening
     */
    public function set(Happening $happening);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Happening[]
     */
    public function findListByEvent(Event $event, $locale);

    /**
     * @param array $happenings
     *
     * @return mixed
     */
    public function findIdsWithoutParticipation(array $happenings);

    /**
     * @param Event  $event
     *
     * @return Happening[]
     */
    public function findByEvent(Event $event);
}
