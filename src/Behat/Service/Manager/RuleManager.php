<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class RuleManager
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * RuleManager constructor.
     *
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param Type  $type
     * @param Event $event
     */
    public function create(Type $type, Event $event)
    {
        $rule = new Rule($event, $type, $type, []);

        $this->ruleRepository->add($rule);
    }
}
