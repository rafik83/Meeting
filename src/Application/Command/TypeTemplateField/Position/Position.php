<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\TypeTemplateField\Position;

use Proximum\Vimeet\Application\Components\Sheet\Template\Group;
use Proximum\Vimeet\Domain\Model\Type;

class Position
{
    /**
     * @var Type
     */
    public $type;

    /**
     * @var string
     */
    public $templateName;

    /**
     * @var Group
     */
    public $group;

    /**
     * @var array
     */
    public $fieldsOrder;

    /**
     * @param Type   $type
     * @param string $templateName
     * @param Group  $group
     * @param array  $fieldsOrder
     */
    public function __construct(Type $type, $templateName, Group $group, array $fieldsOrder)
    {
        $this->type         = $type;
        $this->templateName = $templateName;
        $this->group        = $group;
        $this->fieldsOrder  = $fieldsOrder;
    }
}
