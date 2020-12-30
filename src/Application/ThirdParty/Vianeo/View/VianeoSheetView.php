<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\View;

class VianeoSheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $email;

    /** @var string */
    public $fullName;

    /** @var string */
    public $company;

    /** @var null|string */
    public $category;

    /** @var string */
    public $projectSummary;

    /** @var string */
    public $gender;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $position;

    /** @var string */
    public $phone;

    /**
     * @param int         $id
     * @param string      $email
     * @param string      $fullName
     * @param string      $company
     * @param null|string $category
     * @param string      $projectSummary
     * @param string      $gender
     * @param string      $firstName
     * @param string      $lastName
     * @param string      $position
     * @param string      $phone
     */
    public function __construct(
        int $id,
        string $email,
        string $fullName,
        string $company,
        ?string $category,
        string $projectSummary,
        string $gender,
        string $firstName,
        string $lastName,
        string $position,
        string $phone
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->company = $company;
        $this->category = $category;
        $this->projectSummary = $projectSummary;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->phone = $phone;
        $this->email = $email;
    }
}
