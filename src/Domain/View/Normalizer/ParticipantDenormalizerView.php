<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Normalizer;

class ParticipantDenormalizerView
{
    /**
     * @var int
     */
    public $databaseParticipations;

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
     * @param int $databaseParticipations
     * @param int $fileParticipations
     * @param int $createdSheets
     * @param int $createdUsers
     * @param array $errors
     */
    public function __construct(
        $databaseParticipations,
        $fileParticipations,
        $createdSheets,
        $createdUsers,
        array $errors
    ) {
        $this->databaseParticipations = $databaseParticipations;
        $this->fileParticipations     = $fileParticipations;
        $this->createdSheets          = $createdSheets;
        $this->createdUsers           = $createdUsers;
        $this->errors                 = $errors;
    }

}
