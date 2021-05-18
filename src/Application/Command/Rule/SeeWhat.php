<?php

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Rule;

class SeeWhat implements Command
{
    public Rule $rule;
    public array $seeWhat;
    public int $priority;
    public ?int $phoneAccessMinEvaluation;
    public ?int $emailAccessMinEvaluation;
    public ?int $sendEmailMinEvaluation;

    public bool $requestAutomaticallyTransformedIntoMeeting;
    public bool $isMeetingRequestDisabled;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
        $this->priority = $rule->getPriority();
        $this->seeWhat = $rule->getWhat();
        $this->phoneAccessMinEvaluation = $rule->getPhoneAccessMinEvaluation();
        $this->emailAccessMinEvaluation = $rule->getEmailAccessMinEvaluation();
        $this->sendEmailMinEvaluation = $rule->getSendEmailMinEvaluation();
        $this->requestAutomaticallyTransformedIntoMeeting = $rule->getRequestAutomaticallyTransformedIntoMeeting();
        $this->isMeetingRequestDisabled = $rule->isMeetingRequestDisabled();
    }
}
