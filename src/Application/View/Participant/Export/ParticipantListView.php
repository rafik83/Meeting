<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param string            $locale
     * @param ParticipantView[] $participantViews
     * @param array             $dayColumns
     * @param array             $registrationColumns
     * @param string[]          $productColumns
     */
    public function __construct(
        string $locale,
        array $participantViews,
        array $dayColumns,
        array $registrationColumns,
        array $productColumns
    ) {
        $this->participantViews = $participantViews;
        $this->registrationColumns = $registrationColumns;
        $this->dayColumns = $dayColumns;
        $this->locale = $locale;
        $this->productColumns = $productColumns;
    }
}
