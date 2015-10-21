<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\TypeView;

interface TypeRepositoryInterface
{
    /**
     * @param integer $eventId
     * @param string  $locale
     *
     * @return TypeView[]
     */
    public function getTypeViewsByEvent($eventId, $locale);

    /**
     * @param integer $typeId
     * @param string $locale
     *
     * @return TypeView
     */
    public function getTypeViewById($typeId, $locale);

    /**
     * @param integer $id
     *
     * @return Type
     */
    public function getById($id);

    /**
     * @param $typeId
     *
     * @return string
     */
    public function getParticipantTemplate($typeId);
}
