<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class StateSetter
{
    /**
     * Set state
     *
     * @param Sheet $sheet
     *
     * @deprecated to be rewritten
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
     *
     * @deprecated to be rewritten
     */
    private function isComplete(Sheet $sheet)
    {
        return false;
    }

    /**
     * "si le choix du forfait n'a pas été fait : une commande avec le forfait doit être fait"
     *
     * @param Sheet $sheet
     *
     * @return bool
     *
     * @deprecated to be rewritten
     */
    private function hasPackage(Sheet $sheet)
    {
        return !empty($sheet->getPackageData());
    }
}
