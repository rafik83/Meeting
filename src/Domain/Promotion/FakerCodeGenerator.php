<?php

/*
* This file is part of the Proximum Vimeet project.
*
* Copyright (C) 2016 Proximum
*
* @author Elao <contact@elao.com>
*/

namespace Proximum\Vimeet\Domain\Promotion;

use Faker\Provider\Base;

class FakerCodeGenerator implements CodeGeneratorInterface
{
    /**
     * @return string
     */
    public function generate()
    {
        return strtoupper(Base::lexify('??????'));
    }
}
