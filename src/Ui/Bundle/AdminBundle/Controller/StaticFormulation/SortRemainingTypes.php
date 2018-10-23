<?php
/*
 * This file is part of the Proximium Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;

class SortRemainingTypes
{
    /** @var StaticFormulation */
    public $customizedStaticFormulation;

    /** @var Type[] */
    public $remainingTypes;

    public function __construct(
        StaticFormulation $customizedStaticFormulation,
        array $remainingTypes
    ) {
        $this->customizedStaticFormulation = $customizedStaticFormulation;
        $this->remainingTypes = $remainingTypes;
    }

    public function sort(StaticFormulation $staticFormulation)
    {
        if ($this->customizedStaticFormulation->getId() !== $staticFormulation->getId()) {
            foreach ($this->customizedStaticFormulation->getTypes() as $type) {
                if (isset($this->remainingTypes[$type->getId()])) {
                    unset($this->remainingTypes[$type->getId()]);
                }
            }
        }
    }
}
