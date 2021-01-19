<?php

namespace Proximum\Vimeet\Domain\Planning;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;

class PlanningPrint
{
    /** @var string */
    public $sheetTitle;

    /** @var string */
    public $participantName;

    /** @var string */
    public $planning;

    /** @var string */
    public $participantLocale;

    /** @var string */
    public $leftColor;

    /** @var string */
    public $rightColor;

    /** @var string */
    public $eventTitle;

    /** @var null|string */
    public $eventDescription;

    /** @var null|string */
    public $organiserWebsite;

    /** @var string */
    public $eventDomain;

    /** @var null|string */
    public $contactFirstName;

    /** @var null|string */
    public $contactLastName;

    /** @var null|string */
    public $organiserPhone;

    /** @var null|string */
    public $organiserEmail;

    /** @var null|string */
    public $eventLogo;

    /** @var TipTranslationView[] */
    public $tipTranslationViews;

    /** @var null|string */
    public $spotReference;

    /**
     * @param string               $sheetTitle
     * @param string               $participantName
     * @param string               $planning
     * @param string               $participantLocale
     * @param string               $leftColor
     * @param string               $rightColor
     * @param string               $eventTitle
     * @param string               $eventDescription
     * @param string               $eventDomain
     * @param string|null          $spotReference
     * @param string|null          $eventLogo
     * @param string|null          $organiserWebsite
     * @param string|null          $contactFirstName
     * @param string|null          $contactLastName
     * @param string|null          $organiserPhone
     * @param string|null          $organiserEmail
     * @param TipTranslationView[] $tipTranslationViews
     */
    public function __construct(
        $sheetTitle,
        $participantName,
        $planning,
        $participantLocale,
        $leftColor,
        $rightColor,
        $eventTitle,
        $eventDescription,
        $eventDomain,
        ?string $spotReference = null,
        $eventLogo = null,
        $organiserWebsite = null,
        $contactFirstName = null,
        $contactLastName = null,
        $organiserPhone = null,
        $organiserEmail = null,
        array $tipTranslationViews
    ) {
        $this->sheetTitle        = $sheetTitle;
        $this->participantName   = $participantName;
        $this->planning          = $planning;
        $this->participantLocale = $participantLocale;
        $this->leftColor         = $leftColor;
        $this->rightColor        = $rightColor;
        $this->eventTitle        = $eventTitle;
        $this->eventDescription  = $eventDescription;
        $this->organiserWebsite  = $organiserWebsite;
        $this->eventDomain       = $eventDomain;
        $this->spotReference     = $spotReference;
        $this->eventLogo         = $eventLogo;
        $this->contactFirstName  = $contactFirstName;
        $this->contactLastName   = $contactLastName;
        $this->organiserPhone    = $organiserPhone;
        $this->organiserEmail    = $organiserEmail;
        $this->tipTranslationViews = $tipTranslationViews;
    }
}
