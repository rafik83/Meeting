<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rule;


use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class SeeWhatHandler
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param SeeWhat $seeWhat
     */
    public function handle(SeeWhat $seeWhat)
    {
        $seeWhat->rule->setWhat($seeWhat->seeWhat);

        $this->ruleRepository->update($seeWhat->rule);
    }
}
