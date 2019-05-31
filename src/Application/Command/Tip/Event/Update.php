<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Update extends AbstractEventTip
{
    /** @var Tip */
    public $tip;

    /**
     * @param Tip $tip
     */
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
        $this->onConfirmationPhone = $tip->isOnConfirmationPhone();
        $this->types = $tip->getTypes();
        $this->display = $tip->getDisplay();
        $this->conditionOnOrders = $tip->getConditionOnOrders();
        $this->conditionHasCart = $tip->hasConditionCart();
        $this->conditionHasPendingMeetingProposition = $tip->hasConditionPendingMeetingProposition();
        $this->conditionHasRemainingToPay = $tip->hasConditionRemainingToPay();
        $this->conditionIsCompleteSheet = $tip->hasConditionIncompleteSheet();
        $this->conditionIsPhoneConfirmed = $tip->hasConditionPhoneConfirmed();

        foreach ($tip->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
                'content' => $translation->getContent(),
            ];
        }
    }
}
