<?php

namespace Proximum\Vimeet\Application\View\Rooming\ExportList;

class UserSheetView
{
    /** @var int */
    public $userId;

    /** @var string|null */
    public $gender;

    /** @var string|null */
    public $firstName;

    /** @var string|null */
    public $lastName;

    /** @var string|null */
    public $email;

    /** @var string|null */
    public $mobile;

    /** @var string */
    public $sheetIds;

    /** @var string|null */
    public $sheetTitles;

    /** @var string|null */
    public $sheetFollowers;

    /** @var string|null */
    public $sheetPlans;

    /** @var string|null */
    public $typeTitles;

    /** @var string|null */
    public $spotReferences;

    /** @var string|null */
    public $comment;

    /** @var string|null */
    public $tasting;

    public function __construct(
        int $userId,
        ?string $gender,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $mobile,
        string $sheetIds,
        ?string $sheetTitles,
        ?string $sheetFollowers,
        ?string $sheetPlans,
        ?string $typeTitles,
        ?string $spotReferences,
        ?string $comment,
        ?string $tasting
    ) {
        $this->userId = $userId;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->sheetIds = $sheetIds;
        $this->sheetTitles = $sheetTitles;
        $this->sheetFollowers = $sheetFollowers;
        $this->sheetPlans = $sheetPlans;
        $this->typeTitles = $typeTitles;
        $this->spotReferences = $spotReferences;
        $this->comment = $comment;
        $this->tasting = $tasting;
    }
}
