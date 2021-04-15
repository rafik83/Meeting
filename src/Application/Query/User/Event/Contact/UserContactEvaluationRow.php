<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class UserContactEvaluationRow
{
    public int $votingUserId;
    public ?string $votingFirstName;
    public ?string $votingLastName;
    public int $votingSheetId;
    public string $votingSheetName;
    public int $votingTypeId;
    public int $evaluation;
    public int $meetingId;
    public int $evaluatedUserId;
    public ?string $evaluatedFirstName;
    public ?string $evaluatedLastName;
    public int $evaluatedSheetId;
    public string $evaluatedSheetName;
    public int $evaluatedTypeId;

    public function __construct(
        int $votingUserId,
        ?string $votingFirstName,
        ?string $votingLastName,
        int $votingSheetId,
        string $votingSheetName,
        int $votingTypeId,
        int $evaluation,
        int $meetingId,
        int $evaluatedUserId,
        ?string $evaluatedFirstName,
        ?string $evaluatedLastName,
        int $evaluatedSheetId,
        string $evaluatedSheetName,
        int $evaluatedTypeId
    ) {
        $this->votingUserId = $votingUserId;
        $this->votingFirstName = $votingFirstName;
        $this->votingLastName = $votingLastName;
        $this->votingSheetId = $votingSheetId;
        $this->votingSheetName = $votingSheetName;
        $this->votingTypeId = $votingTypeId;
        $this->evaluation = $evaluation;
        $this->meetingId = $meetingId;
        $this->evaluatedUserId = $evaluatedUserId;
        $this->evaluatedFirstName = $evaluatedFirstName;
        $this->evaluatedLastName = $evaluatedLastName;
        $this->evaluatedSheetId = $evaluatedSheetId;
        $this->evaluatedSheetName = $evaluatedSheetName;
        $this->evaluatedTypeId = $evaluatedTypeId;
    }
}
