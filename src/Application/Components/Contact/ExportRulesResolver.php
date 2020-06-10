<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Contact;

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

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function getExportRule(Sheet $seerSheet, Sheet $seeableSheet): ExportRule
    {
        $phoneAccessMinEvaluation = null;
        $emailAccessMinEvaluation = null;

        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($seerSheet, $seeableSheet);
        $reverseRules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($seeableSheet, $seerSheet);
        $rules = array_merge($rules, $reverseRules);

        if (!empty($rules)) {
            foreach ($rules as $rule) {
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
}
