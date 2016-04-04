<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Domain\Model\Type;

class Update
{
    /**
     * @var Type
     */
    public $type;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var array
     */
    public $validationCriteria = [];

    /**
     * Update constructor.
     *
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
        $this->validationCriteria['sheetAccepted'] = $type->getValidationCriteria()->isSheetAccepted();

        foreach ($type->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
            ];
        }
    }
}
