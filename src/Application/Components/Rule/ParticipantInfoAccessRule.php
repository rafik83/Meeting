<?php

namespace Proximum\Vimeet\Application\Components\Rule;

class ParticipantInfoAccessRule
{
    private ?int $phoneAccessMinEvaluation;
    private ?int $emailAccessMinEvaluation;
    private ?int $sendEmailMinEvaluation;
    private bool $canRequestMeeting;

    public function __construct(
        ?int $phoneAccessMinEvaluation,
        ?int $emailAccessMinEvaluation,
        ?int $sendEmailMinEvaluation,
        bool $canRequestMeeting
    ) {
        $this->phoneAccessMinEvaluation = $phoneAccessMinEvaluation;
        $this->emailAccessMinEvaluation = $emailAccessMinEvaluation;
        $this->sendEmailMinEvaluation = $sendEmailMinEvaluation;
        $this->canRequestMeeting = $canRequestMeeting;
    }

    public function isPhoneVisible(?int $evaluation): bool
    {
        return $this->phoneAccessMinEvaluation === null || ($evaluation !== null && $evaluation > $this->phoneAccessMinEvaluation);
    }

    public function isEmailVisible(?int $evaluation): bool
    {
        return $this->emailAccessMinEvaluation === null || ($evaluation !== null && $evaluation > $this->emailAccessMinEvaluation);
    }

    public function canSendFollowUpEmail(?int $evaluation): bool
    {
        return $this->sendEmailMinEvaluation === null || ($evaluation !== null && $evaluation > $this->sendEmailMinEvaluation);
    }

    public function canRequestMeeting(): bool
    {
        return $this->canRequestMeeting;
    }
}
