<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;

use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\Validator\Error\NomenclatureError;

class NomenclatureValidator implements ObjectValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($data, array $options = [])
    {
        if (empty($data)) {
            return new NomenclatureError($data, true);
        }

        /** @var Nomenclature $nomenclature */
        $nomenclature = $options['object'];
        $items        = $nomenclature->getNomenclatureModel()->getLastLevel();
        $validState   = false;

        foreach ($items as $nomenclatureItem) {
            $nomenclatureLabel = $nomenclatureItem->getLabel($options['locale']);

            if ($nomenclatureLabel === $data) {
                $validState = true;
                break;
            }
        }

        return new NomenclatureError($data, $validState);
    }
}
