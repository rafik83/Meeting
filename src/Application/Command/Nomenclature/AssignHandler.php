<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum Vimeet
 *
 * @author Elao <contact@elao.com>
 */


namespace Proximum\Vimeet\Application\Command\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;

class AssignHandler
{
    /**
     * @var NomenclatureCloner
     */
    private $nomenclatureCloner;

    /**
     * AssignHandler constructor.
     *
     * @param NomenclatureCloner $nomenclatureCloner
     */
    public function __construct(NomenclatureCloner $nomenclatureCloner)
    {
        $this->nomenclatureCloner = $nomenclatureCloner;
    }

    /**
     * @var Assign $command
     *
     * @return AssignResult
     */
    public function handle(Assign $command)
    {
        $nomenclature = $this->nomenclatureCloner->duplicate($command->nomenclature, $command->event);

        return new AssignResult($nomenclature);
    }
}
