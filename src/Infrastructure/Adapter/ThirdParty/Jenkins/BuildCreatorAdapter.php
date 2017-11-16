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
     * @param string $buildName
     * @param array  $arguments
     *
     * @return string|null
     */
    /**
     * {@inheritdoc}
     */
    public function create(string $buildName, array $arguments = []): string
    {
        $argumentsEncoded = array_map(function($key, $value) {
            return [
                'NAME' => $key,
                'VALUE' => $value,
            ];
        }, $arguments);

        $output = [];
        $result = 0;

        $command = str_replace('%%buildName%%', $buildName, $this->jenkinsCommand);
        $command = str_replace('%%jenkinsUser%%', $this->jenkinsUser, $command);
        $command = str_replace('%%jenkinsPassword%%', $this->jenkinsPassword, $command);
        $command = str_replace('%%jenkinsParameters%%', json_encode($argumentsEncoded), $command);

        exec($command .' 2>&1', $output, $result);

        if ($result > 0) {
            throw new BuildCreationFailedException();
        }

        return implode("\n", $output);
    }
}
