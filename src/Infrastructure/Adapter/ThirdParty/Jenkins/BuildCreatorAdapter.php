<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter\ThirdParty\Jenkins;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Proximum\Vimeet\Application\ThirdParty\Jenkins\Exception\BuildCreationFailedException;

class BuildCreatorAdapter implements BuildCreatorInterface
{
    /** @var string */
    private $jenkinsCommand;

    /** @var string */
    private $jenkinsUser;

    /** @var string */
    private $jenkinsPassword;

    /**
     * @param string $jenkinsCommand
     * @param string $jenkinsUser
     * @param string $jenkinsPassword
     */
    public function __construct(
        string $jenkinsCommand,
        string $jenkinsUser,
        string $jenkinsPassword
    ) {
        $this->jenkinsCommand  = $jenkinsCommand;
        $this->jenkinsUser     = $jenkinsUser;
        $this->jenkinsPassword = $jenkinsPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $buildName, array $arguments = []): string
    {
        $argumentsEncoded = [];

        foreach ($arguments as $key => $value) {
            $argumentsEncoded[] = [
                'NAME' => $key,
                'VALUE' => $value,
            ];
        }

        $output = [];
        $result = 0;

        $command = strtr(
            $this->jenkinsCommand,
            [
                '%buildName%' => $buildName,
                '%jenkinsUser%' => $this->jenkinsUser,
                '%jenkinsPassword%' => $this->jenkinsPassword,
                '%jenkinsParameters%' => json_encode($argumentsEncoded),
            ]
        );

        exec($command .' 2>&1', $output, $result);

        if ($result > 0) {
            throw new BuildCreationFailedException();
        }

        return implode("\n", $output);
    }
}
