<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * Critère de validation automatique de la fiche de participation
 */
class ValidationCriteria
{
    /**
     * @var bool
     */
    private $sheetAccepted = false;

    /**
     * Validation Criteria Constructor
     *
     * @param bool $sheetAccepted
     */
    public function __construct($sheetAccepted = false)
    {
        $this->sheetAccepted = $sheetAccepted;
    }

    /**
     * @return boolean
     */
    public function isSheetAccepted()
    {
        return $this->sheetAccepted;
    }
}
