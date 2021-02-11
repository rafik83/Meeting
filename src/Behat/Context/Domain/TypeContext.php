<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\TypeContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Type;

class TypeContext implements Context
{
    /** @var TypeContextProxyInterface */
    private $typeContextProxy;

    /**
     * @param TypeContextProxyInterface $typeContextProxy
     */
    public function __construct(TypeContextProxyInterface $typeContextProxy)
    {
        $this->typeContextProxy = $typeContextProxy;
    }

    /**
     * @Given /^there is a type in this event$/
     */
    public function createInEvent($title = 'Type 1')
    {
        $event = $this->typeContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $type = $this->typeContextProxy->getTypeManager()->create($event, $title, $this->typeContextProxy->getStorage()->get('registrationTemplate'));

        $this->typeContextProxy->getStorage()->set('type', $type);
    }

    /**
     * @Given there is a type :title in this event
     */
    public function thereIsATypeInThisEvent($title)
    {
        $this->createInEvent($title);
    }

    /**
     * @Given /^this package is assigned to this type$/
     */
    public function assignPackage()
    {
        $type = $this->getType();
        $package = $this->typeContextProxy->getStorage()->get('package');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        if (null === $package) {
            throw new \InvalidArgumentException('Missing Package');
        }

        $this->typeContextProxy->getTypeManager()->assignPackageToType($type, $package);
    }

    /**
     * @Given this type is hidden
     */
    public function thisTypeIsHidden()
    {
        $type = $this->getType();
        $type->setHidden(true);

        $this->typeContextProxy->getTypeManager()->set($type);
    }

    /**
     * @Given this type has availability management enabled
     */
    public function thisTypeHasAvailabilityManagementEnabled()
    {
        $type = $this->getType();
        $type->setAvailabilityType(Type::TYPE_MANAGEMENT_AVAILABLE);

        $this->typeContextProxy->getTypeManager()->set($type);
    }

    /**
     * @Given this type has unavailability management enabled
     */
    public function thisTypeHasUnavailabilityManagementEnabled()
    {
        $type = $this->getType();
        $type->setAvailabilityType(Type::TYPE_MANAGEMENT_UNAVAILABLE);

        $this->typeContextProxy->getTypeManager()->set($type);
    }

    /**
     * @Given this type can view display analytics on catalog
     */
    public function thisTypeCanViewDisplayAnalyticsOnCatalog()
    {
        $type = $this->getType();

        $type->update(
            $type->getPosition(),
            $type->isHidden(),
            $type->getAvailabilityType(),
            $type->getNumberOfMeetingsPerPlanning(),
            $type->canUpdateMeeting(),
            $type->canRemoveMeeting(),
            $type->areAllSheetParticipantsAssignedToMeeting(),
            $type->canScanParticipant(),
            $type->isPackageRequired(),
            $type->isPaymentRequired(),
            $type->getPriorityMeetingRequestsNumber(),
            $type->getNumberMaxOfHappeningsPerUser(),
            $type->getNumberMaxOfMeetingsPerSheet(),
            $type->canEvaluateMeeting(),
            $type->mustEvaluateMeeting(),
            $type->canSubmitValidation(),
            $type->canDisplayAnalyticsOnSheet(),
            true
        );

        $this->typeContextProxy->getTypeManager()->set($type);
    }

    /**
     * @Given the :locale translation of this type is :translation
     */
    public function theTranslationOfThisTypeIs(string $locale, string $translation)
    {
        $type = $this->getType();

        $this->typeContextProxy->getTypeManager()->setTypeTranslation($type, $locale, $translation);
    }

    /**
     * @Given this type has this registration template
     */
    public function thisTypeHasThisTemplate()
    {
        $type = $this->getType();
        $template = $this->typeContextProxy->getStorage()->get('registrationTemplate');
        if (null === $template) {
            throw new \InvalidArgumentException('Missing Template');
        }

        $type->setRegistrationTemplate($template);
        $this->typeContextProxy->getTypeManager()->set($type);
    }

    private function getType(): Type
    {
        $type = $this->typeContextProxy->getStorage()->get('type');

        if (null === $type) {
            throw new \InvalidArgumentException('Missing Type');
        }

        return $type;
    }
}
