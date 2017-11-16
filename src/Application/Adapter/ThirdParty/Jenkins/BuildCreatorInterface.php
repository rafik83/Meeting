<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\BuildCreationFailedException;

interface BuildCreatorInterface
{
    /**
     * @param string $buildName
     * @param array  $arguments array of ['INPUT_NAME' => 'INPUT_VALUE']
     *
     * @return string Output of the build creation
     * @throws BuildCreationFailedException
     */
    public function create(string $buildName, array $arguments = []): string;
}
