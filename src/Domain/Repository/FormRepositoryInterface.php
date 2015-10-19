<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

interface FormRepositoryInterface
{
    /**
     * @param integer $typeId
     *
     * @return string
     */
    public function getTemplate($typeId);
}
