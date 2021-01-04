<?php

namespace Proximum\Vimeet\Application\View\Participant\Export;

class ParticipantListView
{
    /** @var string */
    public $locale;

    /** @var ParticipantView[] */
    public $participantViews;

    /** @var array of key => label of the registration fields */
    public $registrationColumns;

    /** @var string[] */
    public $productColumns;

    /** @var string[] */
    public $dayColumns;

    /** @var string[] */
    public $happeningColumns;

    /**
     * @param string            $locale
     * @param ParticipantView[] $participantViews
     * @param array             $dayColumns
     * @param array             $registrationColumns
     * @param string[]          $productColumns
     * @param array             $happeningColumns
     */
    public function __construct(
        string $locale,
        array $participantViews,
        array $dayColumns,
        array $registrationColumns,
        array $productColumns,
        array $happeningColumns
    ) {
        $this->participantViews = $participantViews;
        $this->registrationColumns = $registrationColumns;
        $this->dayColumns = $dayColumns;
        $this->happeningColumns = $happeningColumns;
        $this->locale = $locale;
        $this->productColumns = $productColumns;
    }
}
