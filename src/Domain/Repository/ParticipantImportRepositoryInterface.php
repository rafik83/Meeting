<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
