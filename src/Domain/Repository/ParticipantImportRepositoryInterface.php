<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ParticipantImport;

interface ParticipantImportRepositoryInterface
{
    /**
     * @param ParticipantImport $participantImport
     */
    public function add(ParticipantImport $participantImport);

    /**
     * @param $id
     *
     * @return ParticipantImport
     */
    public function findById($id);
}
