<?php

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractEventTip implements Command
{
    /** @var string */
    public $title;

    /** @var Type[] */
    public $types;

    /** @var bool */
    public $onMeetingManagement;

    /** @var bool */
    public $onCatalog;

    /** @var bool */
    public $onPrintPlanning;

    /** @var bool */
    public $onSheet;

    /** @var bool */
    public $onProgram;

    /** @var bool */
    public $onAgenda;

    /** @var bool */
    public $onPackage;

    /** @var bool */
    public $onContacts;

    /** @var bool */
    public $onConfirmationPhone;

    /** @var bool */
    public $onNetworking;

    /** @var string */
    public $display;

    /** @var null|bool */
    public $conditionHasCart;

    /** @var null|bool */
    public $conditionHasRemainingToPay;

    /** @var null|bool */
    public $conditionIsPhoneConfirmed;

    /** @var null|bool */
    public $conditionIsCompleteSheet;

    /** @var null|bool */
    public $conditionHasPendingMeetingProposition;

    /** @var null|array */
    public $conditionOnOrders;

    /** @var array */
    public $translations;
}
