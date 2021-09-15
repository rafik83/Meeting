<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class UserContactEvaluationView
{
    public int $votingUserId;
    public ?string $votingFirstName;
    public ?string $votingLastName;
    public int $votingSheetId;
    public string $votingSheetName;
    public string $votingCategories;
    public string $votingType;
    public int $evaluation;
    public int $meetingId;
    public int $evaluatedUserId;
    public ?string $evaluatedFirstName;
    public ?string $evaluatedLastName;
    public int $evaluatedSheetId;
    public string $evaluatedSheetName;
    public string $evaluatedCategories;
    public string $evaluatedType;

    public function __construct(
        UserContactEvaluationRow $userContactEvaluationRow,
        string $votingCategories,
        string $votingType,
        string $evaluatedCategories,
        string $evaluatedType
    ) {
        $this->votingUserId = $userContactEvaluationRow->votingUserId;
        $this->votingFirstName = $userContactEvaluationRow->votingFirstName;
        $this->votingLastName = $userContactEvaluationRow->votingLastName;
        $this->votingSheetId = $userContactEvaluationRow->votingSheetId;
        $this->votingSheetName = $userContactEvaluationRow->votingSheetName;
        $this->votingCategories = $votingCategories;
        $this->votingType = $votingType;
        $this->evaluation = $userContactEvaluationRow->evaluation;
        $this->meetingId = $userContactEvaluationRow->meetingId;
        $this->evaluatedUserId = $userContactEvaluationRow->evaluatedUserId;
        $this->evaluatedFirstName = $userContactEvaluationRow->evaluatedFirstName;
        $this->evaluatedLastName = $userContactEvaluationRow->evaluatedLastName;
        $this->evaluatedSheetId = $userContactEvaluationRow->evaluatedSheetId;
        $this->evaluatedSheetName = $userContactEvaluationRow->evaluatedSheetName;
        $this->evaluatedCategories = $evaluatedCategories;
        $this->evaluatedType = $evaluatedType;
    }
}
