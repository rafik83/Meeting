<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Update extends AbstractEventTip
{
    /** @var Tip */
    public $tip;

    public function __construct(Tip $tip)
    {
        $this->tip = $tip;
        $this->title = $tip->getTitle();
        $this->onMeetingManagement = $tip->isOnMeetingManagement();
        $this->onCatalog = $tip->isOnCatalog();
        $this->onPrintPlanning = $tip->isOnPrintPlanning();
        $this->onSheet = $tip->isOnSheet();
        $this->onProgram = $tip->isOnProgram();
        $this->onAgenda = $tip->isOnAgenda();
        $this->onPackage = $tip->isOnPackage();
        $this->onContacts = $tip->isOnContacts();
        $this->onConfirmationPhone = $tip->isOnConfirmationPhone();
        $this->types = $tip->getTypes();
        $this->display = $tip->getDisplay();
        $this->conditionOnOrders = $tip->getConditionOnOrders();
        $this->conditionHasCart = $tip->hasConditionCart();
        $this->conditionHasPendingMeetingProposition = $tip->hasConditionPendingMeetingProposition();
        $this->conditionHasRemainingToPay = $tip->hasConditionRemainingToPay();
        $this->conditionIsCompleteSheet = $tip->hasConditionCompleteSheet();
        $this->conditionIsPhoneConfirmed = $tip->hasConditionPhoneConfirmed();

        foreach ($tip->getEvent()->getLocales() as $locale) {
            $translation = $tip->getTranslation($locale);
            $translationTitle = $translation ? $translation->getTitle() : '';
            $translationContent = $translation ? $translation->getContent() : '';

            $this->translations[$locale] = [
                'title' => $translationTitle,
                'content' => $translationContent,
            ];
        }
    }
}
