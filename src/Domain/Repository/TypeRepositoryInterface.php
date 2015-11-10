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
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeView;
use Proximum\Vimeet\Domain\Model\TypeTemplatesView;
use Proximum\Vimeet\Domain\Model\User;

interface TypeRepositoryInterface
{
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
     * @param integer $typeId
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

    public function getTypesByEvent(Event $event);

    /**
     * @param User|int $user
     *
     * @return array
     */
    public function getSeeableTypeIdsByUser($user);
}
