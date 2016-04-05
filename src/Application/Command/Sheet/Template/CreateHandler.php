<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Sheet\Template;
use Proximum\Vimeet\Domain\Repository\Sheet\TemplateRepositoryInterface;

class CreateHandler
{
    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * CreateHandler constructor.
     *
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(TemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    /**
     * @param Create $create
     *
     * @return CreateResult
     */
    public function handle(Create $create)
    {
        $template = new Template($create->title, [], [$create->locale]);
        $this->templateRepository->add($template);

        return new CreateResult($template);
    }
}
