<?php

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
