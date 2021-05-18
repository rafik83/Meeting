<?php

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRule;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class FollowUpMailAccessRules
{
    private ParticipantInfoAccessRulesResolver $participantInfoAccessRulesResolver;

    public function __construct(ParticipantInfoAccessRulesResolver $participantInfoAccessRulesResolver)
    {
        $this->participantInfoAccessRulesResolver = $participantInfoAccessRulesResolver;
    }

    public function createAccessRule(Sheet $evaluatedSheet, Sheet $evaluatingSheet): ParticipantInfoAccessRule
    {
        return $this->participantInfoAccessRulesResolver->getParticipantInfoAccessRule(
            $evaluatingSheet,
            $evaluatedSheet
        );
    }

    public function canSendEmail(
        Meeting $meeting,
        Sheet $evaluatedSheet,
        ParticipantInfoAccessRule $participantInfoAccessRule,
        int $evaluation
    ): bool {
        if ($meeting->isFollowupSent($evaluatedSheet)) {
            return false;
        }

        return $participantInfoAccessRule->canSendFollowUpEmail($evaluation);
    }
}
