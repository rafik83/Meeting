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

class StatusGuesser
{
    const STATUS_COMPLETE   = 'complete';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_VALIDATED  = 'validated';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * StatusGuesser constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Guess status of a sheet
     *
     * @param Sheet $sheet
     *
     * @return string
     */
    public function guessStatus(Sheet $sheet)
    {
        if (!$this->isComplete($sheet) || !$this->hasPackage($sheet)) {
            return self::STATUS_INCOMPLETE;
        }

        return self::STATUS_COMPLETE;
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
            $this->validator->validateSheet($sheet);

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
