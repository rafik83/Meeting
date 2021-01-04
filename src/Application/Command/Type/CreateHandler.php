<?php

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class CreateHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var SheetTemplateCloner */
    private $sheetTemplateCloner;

    /** @var RegistrationTemplateCloner */
    private $registrationTemplateCloner;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetTemplateCloner $sheetTemplateCloner,
        RegistrationTemplateCloner $registrationTemplateCloner
    ) {
        $this->typeRepository             = $typeRepository;
        $this->sheetTemplateCloner        = $sheetTemplateCloner;
        $this->registrationTemplateCloner = $registrationTemplateCloner;
    }

    /**
     * @param Create $create
     *
     * @throws PackageNotRequiredException
     * @throws TypeAlreadyExistsException
     */
    public function handle(Create $create): void
    {
        if(!$create->isPackageRequired && $create->isPaymentRequired) {
            throw new PackageNotRequiredException();
        }

        $type = new Type($create->event);
        $type->update(
            $create->rank,
            $create->hidden,
            $create->availabilityType,
            $create->numberOfMeetingsPerPlanning,
            $create->canMoveMeeting,
            $create->canRemoveMeeting,
            $create->areAllSheetParticipantsAssignedToMeeting,
            $create->canScanParticipant,
            $create->isPackageRequired,
            $create->isPaymentRequired,
            $create->priorityMeetingRequestsNumber,
            $create->numberMaxOfHappeningsPerUser,
            $create->numberMaxOfMeetingsPerSheet,
            $create->canEvaluateMeeting,
            $create->canEvaluateMeeting ? $create->mustEvaluateMeeting : false,
            $create->canSubmitValidation,
            $create->displayAnalyticsOnSheet,
            $create->displayAnalyticsOnCatalog
        );

        $localesTitleAlreadyExists = [];

        foreach ($create->translations as $locale => $translation) {
            if ($this->typeRepository->typeExists($create->event, $locale, $translation['title'])) {
                $localesTitleAlreadyExists[] = $locale;
            } else {
                $type->translate($locale, $translation['title'], $translation['description']);
            }
        }

        if (!empty($localesTitleAlreadyExists)) {
            throw new TypeAlreadyExistsException($localesTitleAlreadyExists);
        }

        if (isset($create->validationCriteria['sheetAccepted'])) {
            $type->getValidationCriteria()->setSheetAccepted($create->validationCriteria['sheetAccepted']);
        }

        $type->setSheetTemplate($this->getSheetTemplate($create, $type));
        $type->setRegistrationTemplate($this->getRegistrationTemplate($create, $type));
        $type->setPackage($create->package);
        $type->setFormTemplates($create->formTemplates);

        $this->typeRepository->add($type);

        $create->type = $type;
    }

    /**
     * @param Create $create
     * @param Type   $type
     *
     * @return SheetTemplate
     */
    private function getSheetTemplate(Create $create, Type $type)
    {
        return $create->sheetTemplate->getEvent() === $create->event
            ? $create->sheetTemplate
            : $this->sheetTemplateCloner->duplicate(
                $create->sheetTemplate,
                $create->event,
                $type->getTitle($create->event->getAvailableLocale($create->locale))
            );
    }

    /**
     * @param Create $create
     * @param Type   $type
     *
     * @return RegistrationTemplate
     */
    private function getRegistrationTemplate(Create $create, Type $type)
    {
        return $create->registrationTemplate->getEvent() === $create->event
            ? $create->registrationTemplate
            : $this->registrationTemplateCloner->duplicate(
                $create->registrationTemplate,
                $create->event,
                $type->getTitle($create->event->getAvailableLocale($create->locale))
            );
    }
}
