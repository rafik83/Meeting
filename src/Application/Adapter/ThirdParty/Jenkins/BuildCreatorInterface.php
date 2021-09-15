<?php

namespace Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\BuildCreationFailedException;

interface BuildCreatorInterface
{
    /**
     * @param string $buildName
     * @param array  $arguments array of ['INPUT_NAME' => 'INPUT_VALUE']
     *
     * @throws BuildCreationFailedException
     *
     * @return string Output of the build creation
     */
    public function create(string $buildName, array $arguments = []): string;
}
