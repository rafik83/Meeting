<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Rule;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class Duplicator
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
     * @param Event $event
     * @param array $duplicationHelper
     */
    public function duplicate(Event $event, array $duplicationHelper)
    {
        $rules = $this->ruleRepository->getByEvent($event->getDuplicatedFrom());

        foreach ($rules as $rule) {
            $seerKey = $this->getDuplicationHelperKey($rule->getSeer());
            $seer = $duplicationHelper[$seerKey][$rule->getSeer()->getId()];

            $seeableKey = $this->getDuplicationHelperKey($rule->getSeeable());
            $seeable = $duplicationHelper[$seeableKey][$rule->getSeeable()->getId()];

            $newRule = new Rule($event, $seer, $seeable, $rule->getWhat(), $rule->getPriority());
            $this->ruleRepository->add($newRule);
        }
    }

    /**
     * @param WhoInterface $who
     *
     * @return string
     */
    public function getDuplicationHelperKey(WhoInterface $who): string
    {
        if ($who instanceof Type) {
            return 'type';
        } elseif ($who instanceof Category) {
            return 'category';
        }

        throw new \InvalidArgumentException('Given object must be a type or a category');
    }
}
