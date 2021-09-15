<?php

namespace Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData;

class UserDataView
{
    /** @var int */
    public $userId;

    /** @var string|null */
    public $userFirstName;

    /** @var string|null */
    public $userLastName;

    /** @var string|null */
    public $userPhone;

    /** @var string|null */
    public $userMobilePhone;

    /** @var int */
    public $sheetId;

    /** @var string|null */
    public $sheetTitle;

    /** @var string|null */
    public $typeTitle;

    /** @var string|null */
    public $categoryTitle;

    /** @var string|null */
    public $sheetAddress;

    /** @var string|null */
    public $sheetZipCode;

    /** @var string|null */
    public $sheetCity;

    /** @var string|null */
    public $sheetCountry;

    /**
     * @var array
     *
     * Example:
     * [
     *     'objectKey123' => 'Object content foobar',
     *     'objectKey321' => 'Object content barfoo',
     * ]
     */
    public $formTemplateDataByKey;

    /** @var string */
    public $userEmail;

    public function __construct(
        int $userId,
        string $userEmail,
        ?string $userFirstName,
        ?string $userLastName,
        ?string $userPhone,
        ?string $userMobilePhone,
        int $sheetId,
        ?string $sheetTitle,
        ?string $typeTitle,
        ?string $categoryTitle,
        ?string $sheetAddress,
        ?string $sheetZipCode,
        ?string $sheetCity,
        ?string $sheetCountry,
        array $formTemplateDataByKey
    ) {
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->userFirstName = $userFirstName;
        $this->userLastName = $userLastName;
        $this->userPhone = $userPhone;
        $this->userMobilePhone = $userMobilePhone;
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->typeTitle = $typeTitle;
        $this->categoryTitle = $categoryTitle;
        $this->sheetAddress = $sheetAddress;
        $this->sheetZipCode = $sheetZipCode;
        $this->sheetCity = $sheetCity;
        $this->sheetCountry = $sheetCountry;
        $this->formTemplateDataByKey = $formTemplateDataByKey;
    }
}
