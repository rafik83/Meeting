<?php

namespace Proximum\Vimeet\Application\Components\Rule;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\WhoInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

/**
 * Get a ParticipantInfoAccessRule from 2 sheets, to be used in contact export and to send followup mails
 */
class ParticipantInfoAccessRulesResolver
{
    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var Rule[] */
    private $rules;

    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function getParticipantInfoAccessRule(Sheet $seerSheet, Sheet $seeableSheet): ParticipantInfoAccessRule
    {
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
                $rulesApplicable = array_merge(
                    $rulesApplicable,
                    $this->extractMatchingRules($rules[$who->getId()], $seeableWhos)
                );
            }
        }

        return $this->createAccessInfoRuleFromRulesList($rulesApplicable);
    }

    private function loadRules(Event $event): array
    {
        if ($this->rules === null) {
            $allRules = $this->ruleRepository->getByEvent($event);
            // index rules by "who"
            $this->rules = array_reduce($allRules, function ($carry, Rule $rule) {
                $carry[$rule->getSeer()->getId()][] = $rule;
                return $carry;
            }, []);
        }

        return $this->rules;
    }

    /**
     * @param Rule[] $rules
     * @param WhoInterface[] $seeableWhos
     *
     * @return Rule[]
     */
    private function extractMatchingRules(array $rules, array $seeableWhos): array
    {
        return array_filter($rules, static function (Rule $rule) use ($seeableWhos) {

            foreach ($seeableWhos as $seeableWho) {
                if ($seeableWho->getId() === $rule->getSeeable()->getId()
                    && $seeableWho->getIdentifier() === $rule->getSeeable()->getIdentifier()
                ) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param Rule[] $rulesApplicable
     */
    private function createAccessInfoRuleFromRulesList(array $rulesApplicable): ParticipantInfoAccessRule
    {
        $phoneAccessMinEvaluation = null;
        $emailAccessMinEvaluation = null;
        $sendEmailMinEvaluation = 0;
        $canRequestMeeting = true;

        if (!empty($rulesApplicable)) {
            foreach ($rulesApplicable as $rule) {
                if (null !== $rule->getPhoneAccessMinEvaluation() && $rule->getPhoneAccessMinEvaluation() > $phoneAccessMinEvaluation) {
                    $phoneAccessMinEvaluation = $rule->getPhoneAccessMinEvaluation();
                }
                if (null !== $rule->getEmailAccessMinEvaluation() && $rule->getEmailAccessMinEvaluation() > $emailAccessMinEvaluation) {
                    $emailAccessMinEvaluation = $rule->getEmailAccessMinEvaluation();
                }
                if ($sendEmailMinEvaluation === 0) {
                    $sendEmailMinEvaluation = $rule->getSendEmailMinEvaluation();
                }
                if (null !== $rule->getSendEmailMinEvaluation() && $rule->getSendEmailMinEvaluation() > $sendEmailMinEvaluation) {
                    $sendEmailMinEvaluation = $rule->getSendEmailMinEvaluation();
                }

                $canRequestMeeting = !$rule->isMeetingRequestDisabled();
            }
        }

        // by default, don't send email if there's no rule
        if ($sendEmailMinEvaluation === 0) {
            $sendEmailMinEvaluation = 5;
        }

        return new ParticipantInfoAccessRule(
            $phoneAccessMinEvaluation,
            $emailAccessMinEvaluation,
            $sendEmailMinEvaluation,
            $canRequestMeeting
        );
    }
}
