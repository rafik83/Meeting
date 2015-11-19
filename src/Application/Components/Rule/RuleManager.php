<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule;

use Proximum\Vimeet\Application\Components\Rule\Exception\NoRuleFoundException;
use Proximum\Vimeet\Application\Components\Rule\Strategy\StrategyInterface;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class RuleManager
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var RuleSorter
     */
    private $ruleSorter;

    /**
     * @var array
     */
    private $cache;

    /**
     * RuleManager constructor.
     *
     * @param RuleRepositoryInterface $ruleRepository
     * @param TypeRepositoryInterface $typeRepository
     * @param RuleSorter              $ruleSorter
     */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        TypeRepositoryInterface $typeRepository,
        RuleSorter $ruleSorter
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->typeRepository = $typeRepository;
        $this->ruleSorter     = $ruleSorter;
    }

    /**
     * Get the most prioritary rule rule to apply
     *
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return Rule
     * @throws NoRuleFoundException
     */
    public function getRule(Sheet $sheet, User $user)
    {
        // Check cache
        if (isset($this->cache[$sheet->getType()->getId()])) {
            return $this->cache[$sheet->getType()->getId()];
        }

        // Get types of sheet the user have for this event
        $types = $this->typeRepository->getTypesByUser($sheet->getEvent(), $user);

        // Get related rules
        $rules = [];
        foreach ($types as $type) {
            $rules = array_merge($rules, $this->ruleRepository->getBySeerTypeAndSeeableType($type, $sheet->getType()));
        }

        // Sort rules by priority
        $this->ruleSorter->sort($rules);

        if (!isset($rules[0])) {
            throw new NoRuleFoundException('No rule found.');
        }

        // Update cache
        $this->cache[$sheet->getType()->getId()] = $rules[0];

        return $this->cache[$sheet->getType()->getId()];
    }

    /**
     * @param Rule              $rule
     * @param Sheet             $sheet
     * @param StrategyInterface $strategy
     */
    public function apply(Rule $rule, Sheet $sheet, StrategyInterface $strategy)
    {
        $what = array_merge(['sheet' => [], 'participant' => []], $rule->getWhat());

        // Apply rule on sheet data
        $sheet->setData($strategy->apply($sheet->getData(), $what['sheet']));

        // Appy rule on participants data
        foreach ($sheet->getParticipants() as $participant) {
            $participant->setData($strategy->apply($participant->getData(), $what['participant']));
        }
    }
}
