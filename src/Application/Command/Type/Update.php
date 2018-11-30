<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

class Update implements Command
{
    /** @var Type */
    public $type;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $validationCriteria = [];

    /** @var int */
    public $rank;

    /** @var SheetTemplate */
    public $sheetTemplate;

    /** @var Package */
    public $package;

    /** @var RegistrationTemplate */
    public $registrationTemplate;

    /** @var FormTemplate[] */
    public $formTemplates;

    /** @var string */
    public $locale;

    /** @var bool */
    public $hidden;

    /**
     * @param Type   $type
     * @param string $locale
     */
    public function __construct(Type $type, $locale)
    {
        $this->sheetTemplate                       = $type->getSheetTemplate();
        $this->package                             = $type->getPackage();
        $this->registrationTemplate                = $type->getRegistrationTemplate();
        $this->formTemplates                       = $type->getFormTemplates();
        $this->locale                              = $locale;
        $this->type                                = $type;
        $this->rank                                = $type->getPosition();
        $this->validationCriteria['sheetAccepted'] = $type->getValidationCriteria()->isSheetAccepted();
        $this->hidden                              = $type->isHidden();

        foreach ($type->getEvent()->getLocales() as $eventLocale) {
            $this->translations[$eventLocale] = [
                'title'       => $type->getTitle($eventLocale),
                'description' => $type->getDescription($eventLocale),
            ];
        }
    }
}
