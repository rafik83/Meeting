<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
use Proximum\Vimeet\Domain\Type\TypeInterface;

/**
 * "Type de participation".
 */
class Type implements WhoInterface, TypeInterface
{
    public const TYPE_MANAGEMENT_UNAVAILABLE = 'unavailable';
    public const TYPE_MANAGEMENT_AVAILABLE = 'available';
    public const TYPE_MANAGEMENT_NONE = 'none';

    /** @var int */
    private $id;

    /** @var int|null */
    private $position = 0;

    /** @var Event */
    private $event;

    /** @var ArrayCollection of Admin */
    private $admins;

    /** @var ArrayCollection of TypeTranslation */
    private $translations;

    /** @var SheetTemplate */
    private $sheetTemplate;

    /** @var RegistrationTemplate */
    private $registrationTemplate;

    /** @var string */
    private $previewTemplate = '';

    /** @var string */
    private $viewTemplate = '';

    /** @var ArrayCollection of Category */
    private $categories;

    /** @var ValidationCriteria */
    private $validationCriteria;

    /** @var Package */
    private $package;

    /** @var bool */
    private $hidden = false;

    /** @var ArrayCollection of PaymentConditions */
    private $paymentConditions;

    /** @var ArrayCollection of FormTemplates */
    private $formTemplates;

    /** @var string */
    private $availabilityType;

    /**
     * @todo to remove after migration
     */
    private $disableUnavailabilityManagement = false;

    /** @var int|null */
    private $numberOfMeetingsPerPlanning;

    /** @var bool */
    private $canMoveMeeting = false;

    /** @var bool */
    private $canRemoveMeeting = false;

    /** @var bool */
    private $areAllSheetParticipantsAssignedToMeeting = false;

    /** @var bool */
    public $canScanParticipant = false;

    /** @var bool */
    public $isPackageRequired = false;

    /** @var bool */
    public $isPaymentRequired = false;

    /** @var integer */
    private $priorityMeetingRequestsNumber = 0;

    /** @var integer */
    private $numberMaxOfHappeningsPerUser = null;

    /** @var int|null */
    private $numberMaxOfMeetingsPerSheet;

    /** @var bool */
    private $canEvaluateMeeting = true;

    /** @var bool */
    private $mustEvaluateMeeting = false;

    /** @var bool */
    public $submitValidationSheet = true;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->translations = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->admins = new ArrayCollection();
        $this->validationCriteria = new ValidationCriteria(false);
        $this->paymentConditions = new ArrayCollection();
        $this->formTemplates = new ArrayCollection();
        $this->availabilityType = self::TYPE_MANAGEMENT_NONE;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->getTranslations()->containsKey($locale) ? $this->getTranslations()->get($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->getTranslations()->containsKey($locale) ?
            $this->getTranslations()->get($locale)->getDescription() : '';
    }

    /**
     * @return SheetTemplate
     */
    public function getSheetTemplate()
    {
        return $this->sheetTemplate;
    }

    /**
     * @return SheetTemplate
     *
     * @deprecated Use getSheetTemplate()
     */
    public function getNewSheetTemplate()
    {
        return $this->getSheetTemplate();
    }

    /**
     * @param SheetTemplate $sheetTemplate
     *
     * @return Type
     */
    public function setSheetTemplate(SheetTemplate $sheetTemplate)
    {
        $this->sheetTemplate = $sheetTemplate;

        return $this;
    }

    /**
     * @param RegistrationTemplate $registrationTemplate
     *
     * @return Type
     */
    public function setRegistrationTemplate(RegistrationTemplate $registrationTemplate)
    {
        $this->registrationTemplate = $registrationTemplate;

        return $this;
    }

    /**
     * @return RegistrationTemplate
     */
    public function getRegistrationTemplate()
    {
        return $this->registrationTemplate;
    }

    /**
     * @param Package $package
     *
     * @return Type
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return int|float
     */
    public function getMaxParticipant()
    {
        if (null !== $this->package && null !== $this->package->getMaxParticipant()) {
            return $this->package->getMaxParticipant();
        }

        return INF;
    }

    /**
     * @return int|float
     */
    public function getMaxPlanning()
    {
        if (null !== $this->package
            && null !== $this->package->getPlanning()
            && null !== $this->package->getPlanning()->getQuantityMax()
        ) {
            return $this->package->getPlanning()->getQuantityMax();
        }

        return $this->getMaxParticipant();
    }

    /**
     * Get preview.
     *
     * @return string
     */
    public function getPreviewTemplate()
    {
        return $this->previewTemplate;
    }

    /**
     * Get viewTemplate.
     *
     * @return string
     */
    public function getViewTemplate()
    {
        return $this->viewTemplate;
    }

    /**
     * Get categories.
     *
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'type';
    }

    /**
     * @return ValidationCriteria
     */
    public function getValidationCriteria()
    {
        return $this->validationCriteria;
    }

    /**
     * @param int $position
     *
     * @return Type
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return array
     *
     * @deprecated Use getRegistrationTemplate()
     */
    public function getParticipantTemplate()
    {
        return [];
    }

    /**
     * @param string $locale
     *
     * @return array
     */
    public function getCategoriesTitles($locale)
    {
        return $this->categories->map(
            static function (Category $category) use ($locale) {
                return $category->getTitle($locale);
            }
        )->toArray()
            ;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     *
     * @return Type
     */
    public function translate($locale, $title, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($title, $description);
        } else {
            $this->translations->set($locale, new TypeTranslation($this, $locale, $title, $description));
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return Type
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return Admin[]
     */
    public function getAdmins()
    {
        return $this->admins->toArray();
    }

    /**
     * @return null|PaymentConditions
     */
    public function getPaymentConditions(): ?PaymentConditions
    {
        $paymentConditions = $this->paymentConditions->first();

        if (false === $paymentConditions) {
            return null;
        }

        return $paymentConditions;
    }

    /**
     * @return FormTemplate[]
     */
    public function getFormTemplates(): array
    {
        return $this->formTemplates->toArray();
    }

    public function setFormTemplates(array $templates): void
    {
        $this->formTemplates = new ArrayCollection($templates);
    }

    public function getAvailabilityType(): string
    {
        return $this->availabilityType;
    }

    public function setAvailabilityType(string $availabilityType): void
    {
        $this->availabilityType = $availabilityType;
    }

    public function update(
        ?int $rank,
        bool $hidden,
        string $availabilityType,
        ?int $numberOfMeetingsPerPlanning,
        bool $canMoveMeeting = false,
        bool $canRemoveMeeting = false,
        bool $areAllSheetParticipantsAssignedToMeeting = false,
        bool $canScanParticipant = false,
        bool $isPackageRequired = false,
        bool $isPaymentRequired = false,
        int $priorityMeetingRequestsNumber = 0,
        ?int $numberMaxOfHappeningsPerUser = null,
        ?int $numberMaxOfMeetingsPerSheet = null,
        bool $canEvaluateMeeting = true,
        bool $mustEvaluateMeeting = false,
        bool $submitValidationSheet = true
    ) {
        $this->position = $rank;
        $this->hidden = $hidden;
        $this->availabilityType = $availabilityType;
        $this->numberOfMeetingsPerPlanning = $numberOfMeetingsPerPlanning;
        $this->canMoveMeeting = $canMoveMeeting;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->areAllSheetParticipantsAssignedToMeeting = $areAllSheetParticipantsAssignedToMeeting;
        $this->canScanParticipant = $canScanParticipant;
        $this->isPackageRequired = $isPackageRequired;
        $this->isPaymentRequired = $isPaymentRequired;
        $this->priorityMeetingRequestsNumber = $priorityMeetingRequestsNumber;
        $this->numberMaxOfHappeningsPerUser =  $numberMaxOfHappeningsPerUser;
        $this->numberMaxOfMeetingsPerSheet = $numberMaxOfMeetingsPerSheet;
        $this->canEvaluateMeeting = $canEvaluateMeeting;
        $this->mustEvaluateMeeting = $mustEvaluateMeeting;
        $this->submitValidationSheet = $submitValidationSheet;
    }

    public function getNumberOfMeetingsPerPlanning(): ?int
    {
        return $this->numberOfMeetingsPerPlanning;
    }

    /**
     * @todo to remove after migration
     */
    public function isDisableUnavailabilityManagement(): bool
    {
        return $this->disableUnavailabilityManagement;
    }

    public function canMoveMeeting(): bool
    {
        return $this->canMoveMeeting;
    }

    public function canRemoveMeeting(): bool
    {
        return $this->canRemoveMeeting;
    }

    public function areAllSheetParticipantsAssignedToMeeting(): bool
    {
        return $this->areAllSheetParticipantsAssignedToMeeting;
    }

    public function canScanParticipant(): bool
    {
        return $this->canScanParticipant;
    }

    public function isPackageRequired(): bool
    {
        return $this->isPackageRequired;
    }

    public function isPaymentRequired(): bool
    {
        return $this->isPaymentRequired;
    }

    public function getPriorityMeetingRequestsNumber(): int
    {
        return $this->priorityMeetingRequestsNumber;
    }

    public function setPriorityMeetingRequestsNumber(int $priorityMeetingRequestsNumber): void
    {
        $this->priorityMeetingRequestsNumber = $priorityMeetingRequestsNumber;
    }

    public function getNumberMaxOfHappeningsPerUser(){
        return $this->numberMaxOfHappeningsPerUser;
    }

    public function getNumberMaxOfMeetingsPerSheet(): ?int
    {
        return $this->numberMaxOfMeetingsPerSheet;
    }

    public function canEvaluateMeeting(): bool
    {
        return $this->canEvaluateMeeting;
    }

    public function mustEvaluateMeeting(): bool
    {
        return $this->mustEvaluateMeeting;
    }

    public function isSubmitValidationSheet(): bool
    {
        return $this->submitValidationSheet;
    }
}
