<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Normalizer;

class ParticipantDenormalizerView
{
    /**
     * @var int
     */
    public $existingParticipations;

    /**
     * @var int
     */
    public $fileParticipations;

    /**
     * @var int
     */
    public $createdSheets = 0;

    /**
     * @var int
     */
    public $createdUsers = 0;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * ParticipantDenormalizerView constructor.
     *
     * @param int   $existingParticipations
     * @param int   $fileParticipations
     * @param int   $createdSheets
     * @param int   $createdUsers
     * @param array $errors
     */
    public function __construct(
        $existingParticipations,
        $fileParticipations,
        $createdSheets,
        $createdUsers,
        array $errors
    ) {
        $this->existingParticipations = $existingParticipations;
        $this->fileParticipations     = $fileParticipations;
        $this->createdSheets          = $createdSheets;
        $this->createdUsers           = $createdUsers;
        $this->errors                 = $errors;
    }
}
