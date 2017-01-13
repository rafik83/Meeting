<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Validator;

interface ObjectValidatorInterface
{
    /**
     * @param mixed $data
     * @param array $options
     *
     * @return bool
     */
    public function validate($data, array $options = []);
}
