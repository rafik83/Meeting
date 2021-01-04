<?php

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class SeeWhat
{
    /**
     * @var Rule
     */
    public $rule;

    /**
     * @var array
     */
    public $seeWhat;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var int
     */
    public $phoneAccessMinEvaluation;

    /**
     * @var int
     */
    public $emailAccessMinEvaluation;

    /**
     * @var bool
     */
    public $requestAutomaticallyTransformedIntoMeeting;

    /**
     * @param Rule $rule
     */
    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
        $this->priority = $rule->getPriority();
        $this->seeWhat = $rule->getWhat();
        $this->phoneAccessMinEvaluation = $rule->getPhoneAccessMinEvaluation();
        $this->emailAccessMinEvaluation = $rule->getEmailAccessMinEvaluation();
        $this->requestAutomaticallyTransformedIntoMeeting = $rule->getRequestAutomaticallyTransformedIntoMeeting();
    }
}
