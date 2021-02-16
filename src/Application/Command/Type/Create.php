<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

class Create implements Command
{
    /** @var Type */
    public $type;

    /** @var Event */
    public $event;

    /** @var SheetTemplate */
    public $sheetTemplate;

    /** @var Package */
    public $package;

    /** @var RegistrationTemplate */
    public $registrationTemplate;

    /** @var FormTemplate[] */
    public $formTemplates;

    /** @var array */
    public $validationCriteria = [];

    /** @var array */
    public $translations = [];

    /** @var string */
    public $locale;

    /** @var int */
    public $rank;

    /** @var bool */
    public $hidden;

    /** @var string */
    public $availabilityType = Type::TYPE_MANAGEMENT_NONE;

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
    public $priorityMeetingRequestsNumber = 0;

    /** @var int|null */
    public $numberMaxOfHappeningsPerUser;

    /** @var int|null */
    public $numberMaxOfMeetingsPerSheet;

    /** @var bool */
    public $canEvaluateMeeting = true;

    /** @var bool */
    public $mustEvaluateMeeting = false;

    /** @var bool */
    public $canSubmitValidation = true;

    /** @var bool */
    public $displayAnalyticsOnSheet = false;

    /** @var bool */
    public $displayAnalyticsOnCatalog = false;

    public function __construct(Event $event, string $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;

        foreach ($event->getLocales() as $eventLocale) {
            $this->translations[$eventLocale] = [
                'title' => '',
                'description' => '',
            ];
        }
    }
}
