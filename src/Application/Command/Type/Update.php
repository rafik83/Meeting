<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

class Update implements Command
{
    /** @var Type */
    public $type;

    /** @var array */
    public $translations = [];

    /** @var array */
    public $validationCriteria = [];

    /** @var int */
    public $rank;

    /** @var SheetTemplate */
    public $sheetTemplate;

    /** @var Package */
    public $package;

    /** @var RegistrationTemplate */
    public $registrationTemplate;

    /** @var FormTemplate[] */
    public $formTemplates;

    /** @var string */
    public $locale;

    /** @var bool */
    public $hidden;

    /** @var string */
    public $availabilityType;

    /** @var int|null */
    public $numberOfMeetingsPerPlanning;

    /** @var bool */
    public $canUpdateMeeting;

    /** @var bool */
    public $canRemoveMeeting;

    /** @var bool */
    public $areAllSheetParticipantsAssignedToMeeting;

    /** @var bool */
    public $canScanParticipant;

    /** @var bool */
    public $isPackageRequired = false;

    /** @var bool */
    public $isPaymentRequired = false;

    /** @var int */
    public $priorityMeetingRequestsNumber;

    /** @var int|null */
    public $numberMaxOfHappeningsPerUser;

    /** @var int|null */
    public $numberMaxOfMeetingsPerSheet;

    /** @var bool */
    public $canEvaluateMeeting;

    /** @var bool */
    public $mustEvaluateMeeting;

    /** @var bool */
    public $canSubmitValidation;

    /** @var bool */
    public $displayAnalyticsOnSheet;

    /** @var bool */
    public $displayAnalyticsOnCatalog;

    public function __construct(Type $type, string $locale)
    {
        $this->sheetTemplate = $type->getSheetTemplate();
        $this->package = $type->getPackage();
        $this->registrationTemplate = $type->getRegistrationTemplate();
        $this->formTemplates = $type->getFormTemplates();
        $this->locale = $locale;
        $this->type = $type;
        $this->rank = $type->getPosition();
        $this->validationCriteria['sheetAccepted'] = $type->getValidationCriteria()->isSheetAccepted();
        $this->hidden = $type->isHidden();
        $this->availabilityType = $type->getAvailabilityType();
        $this->hidden = $type->isHidden();
        $this->numberOfMeetingsPerPlanning = $type->getNumberOfMeetingsPerPlanning();
        $this->canUpdateMeeting = $type->canUpdateMeeting();
        $this->canRemoveMeeting = $type->canRemoveMeeting();
        $this->areAllSheetParticipantsAssignedToMeeting = $type->areAllSheetParticipantsAssignedToMeeting();
        $this->canScanParticipant = $type->canScanParticipant();
        $this->isPackageRequired = $type->isPackageRequired();
        $this->isPaymentRequired = $type->isPaymentRequired();
        $this->priorityMeetingRequestsNumber = $type->getPriorityMeetingRequestsNumber();
        $this->numberMaxOfHappeningsPerUser = $type->getNumberMaxOfHappeningsPerUser();
        $this->numberMaxOfMeetingsPerSheet = $type->getNumberMaxOfMeetingsPerSheet();
        $this->canEvaluateMeeting = $type->canEvaluateMeeting();
        $this->mustEvaluateMeeting = $type->mustEvaluateMeeting();
        $this->canSubmitValidation = $type->canSubmitValidation();
        $this->displayAnalyticsOnSheet = $type->canDisplayAnalyticsOnSheet();
        $this->displayAnalyticsOnCatalog = $type->canDisplayAnalyticsOnCatalog();

        foreach ($type->getEvent()->getLocales() as $eventLocale) {
            $this->translations[$eventLocale] = [
                'title'       => $type->getTitle($eventLocale),
                'description' => $type->getDescription($eventLocale),
            ];
        }
    }
}
