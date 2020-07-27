<?php

namespace Proximum\Vimeet\Domain\View\Normalizer;

use Proximum\Vimeet\Domain\Model\ParticipantImport;

class ParticipantDenormalizerView
{
    /** @var int */
    public $existingParticipations;

    /** @var int */
    public $fileParticipations;

    /** @var int */
    public $createdSheets = 0;

    /** @var int */
    public $createdUsers = 0;

    /** @var array */
    public $errors = [];

    /** @var ParticipantImport */
    public $participantImport;

    public function __construct(
        ParticipantImport $participantImport,
        int $existingParticipations,
        int $fileParticipations,
        int $createdSheets,
        int $createdUsers,
        array $errors
    ) {
        $this->participantImport = $participantImport;
        $this->existingParticipations = $existingParticipations;
        $this->fileParticipations = $fileParticipations;
        $this->createdSheets = $createdSheets;
        $this->createdUsers = $createdUsers;
        $this->errors = $errors;
    }
}
