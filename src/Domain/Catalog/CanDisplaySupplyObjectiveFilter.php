<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class CanDisplaySupplyObjectiveFilter
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(TemplateDataFactory $templateDataFactory)
    {

        $this->templateDataFactory = $templateDataFactory;
    }

    public function isSatisfiedBy(Sheet $sheet, $locale = null)
    {

        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $nomenclatures = $templateData->getNomenclatureObjects();

        foreach ($nomenclatures as $nomenclature) {
            if ($nomenclature->isSupply() && !empty($nomenclature->getItems())) {

                return true;
            }
        }

        return false;
    }
}
