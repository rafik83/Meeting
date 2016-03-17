<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Template\Exception\MissingRequiredDataException;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Domain\Model\Sheet;

class StateSetter
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * StateSetter constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Set state
     *
     * @param Sheet $sheet
     */
    public function setState(Sheet $sheet)
    {
        if ($sheet->isValidated()) {
            return;
        }

        if (!$this->isComplete($sheet) || !$this->hasPackage($sheet)) {
            $sheet->markAsIncomplete();
        }

        $sheet->markAsComplete();
    }

    /**
     * "si la fiche de présentation n'est pas complète : tous les champs obligatoires non remplies"
     *
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function isComplete(Sheet $sheet)
    {
        try {
            $this->validator->validateSheetData($sheet, $sheet->getData());

            return true;
        } catch (MissingRequiredDataException $exception) {
            return false;
        }
    }

    /**
     * "si le choix du forfait n'a pas été fait : une commande avec le forfait doit être fait"
     *
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function hasPackage(Sheet $sheet)
    {
        return !empty($sheet->getPackageData());
    }
}
