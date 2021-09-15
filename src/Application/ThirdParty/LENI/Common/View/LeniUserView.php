<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\View;

class LeniUserView
{
    /** @var int */
    public $id;

    /** @var string */
    public $sheetName;

    /** @var int|null */
    public $typeId;

    /** @var int|null */
    public $categoryId;

    /** @var string */
    public $email;

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

    /** @var string */
    public $mobile;

    /** @var string */
    public $country;

    /** @var string */
    public $locale;

    /** @var LeniPlanningView */
    public $planning;

    /** @var null|string */
    public $leniId;

    /** @var bool */
    public $enabled;

    /** @var bool */
    public $paid;

    /** @var int|null */
    public $participantProductId;

    /** @var null|LeaderView */
    public $leaderView;

    /** @var array indexed by LENI fieldName */
    public $customData;

    /**
     * @param int              $id
     * @param bool             $enabled
     * @param string|null      $sheetName
     * @param int|null         $typeId
     * @param int|null         $categoryId
     * @param string           $email
     * @param string           $gender
     * @param string           $firstName
     * @param string           $lastName
     * @param string           $position
     * @param string           $phone
     * @param string           $mobile
     * @param string           $country
     * @param string           $locale
     * @param null|LeaderView  $leaderView
     * @param LeniPlanningView $planning
     * @param null|string      $leniId
     * @param bool             $paid
     * @param int|null         $participantProductId
     * @param array            $customData           indexed by LENI fieldName
     */
    public function __construct(
        int $id,
        bool $enabled,
        string $sheetName = null,
        int $typeId = null,
        int $categoryId = null,
        string $email,
        string $gender,
        string $firstName,
        string $lastName,
        string $position,
        string $phone,
        string $mobile,
        string $country,
        string $locale,
        ?LeaderView $leaderView,
        LeniPlanningView $planning,
        ?string $leniId,
        bool $paid,
        ?int $participantProductId,
        array $customData
    ) {
        $this->id = $id;
        $this->sheetName = $sheetName;
        $this->typeId = $typeId;
        $this->categoryId = $categoryId;
        $this->email = $email;
        $this->gender = $gender;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->position = $position;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->planning = $planning;
        $this->country = $country;
        $this->locale = $locale;
        $this->leniId = $leniId;
        $this->enabled = $enabled;
        $this->paid = $paid;
        $this->participantProductId = $participantProductId;
        $this->leaderView = $leaderView;
        $this->customData = $customData;
    }
}
