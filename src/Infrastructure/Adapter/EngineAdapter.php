<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\EngineInterface;
use Symfony\Component\Templating\EngineInterface as SymfonyEngineInterface;

class EngineAdapter implements EngineInterface
{
    /** @var SymfonyEngineInterface */
    private $engine;

    public function __construct(SymfonyEngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $filePath, array $parameters = []): string
    {
        return $this->engine->render($filePath, $parameters);
    }
}
