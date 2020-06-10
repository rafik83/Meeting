<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Contact;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\ExportRule;

/**
 * Get an ExportRule from 2 sheets, to be used in contact export
 */
class ExportRulesResolver
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    private $rules;

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function getExportRule(Sheet $seerSheet, Sheet $seeableSheet): ExportRule
    {
        $phoneAccessMinEvaluation = null;
        $emailAccessMinEvaluation = null;

        $rules = $this->loadRules($seeableSheet->getEvent());

        $rulesApplicable = [];
        $seerWhos = array_merge(
            [$seerSheet->getType()],
            $seerSheet->getType()->getCategories()->toArray()
        );
        $seeableWhos = array_merge(
            [$seeableSheet->getType()],
            $seeableSheet->getType()->getCategories()->toArray()
        );

        // extract direct rules
        foreach ($seerWhos as $who) {
            if (isset($rules[$who->getId()])) {
                $rulesApplicable = array_merge($rulesApplicable, array_filter($rules[$who->getId()], function (Rule $rule) use ($seeableWhos) {
                    return in_array($rule->getSeeable(), $seeableWhos);
                }));
            }
        }
        // extract inverse rules
        foreach ($seeableWhos as $who) {
            if (isset($rules[$who->getId()])) {
                $rulesApplicable = array_merge($rulesApplicable, array_filter($rules[$who->getId()], function (Rule $rule) use ($seerWhos) {
                    return in_array($rule->getSeeable(), $seerWhos);
                }));
            }
        }

        if (!empty($rulesApplicable)) {
            foreach ($rulesApplicable as $rule) {
                if (null !== $rule->getPhoneAccessMinEvaluation() && $rule->getPhoneAccessMinEvaluation() > $phoneAccessMinEvaluation) {
                    $phoneAccessMinEvaluation = $rule->getPhoneAccessMinEvaluation();
                }
                if (null !== $rule->getEmailAccessMinEvaluation() && $rule->getEmailAccessMinEvaluation() > $emailAccessMinEvaluation) {
                    $emailAccessMinEvaluation = $rule->getEmailAccessMinEvaluation();
                }
            }
        }

        return new ExportRule($phoneAccessMinEvaluation, $emailAccessMinEvaluation);
    }

    private function loadRules(Event $event): array
    {
        if (is_null($this->rules)) {
            $allRules = $this->ruleRepository->getByEvent($event);
            // index rules by "who"
            $this->rules = array_reduce($allRules, function ($carry, Rule $rule) {
                $carry[$rule->getSeer()->getId()][] = $rule;
                return $carry;
            }, []);
        }

        return $this->rules;
    }
}
