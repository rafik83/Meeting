<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\HappeningParticipation;

class HappeningParticipationViewQuery
{
    /** @var HappeningParticipation */
    public $happeningParticipation;

    /** @var string */
    public $locale;

    /**
     * @param HappeningParticipation $happeningParticipation
     * @param string                 $locale
     */
    public function __construct(HappeningParticipation $happeningParticipation, $locale)
    {
        $this->happeningParticipation = $happeningParticipation;
        $this->locale                 = $locale;
    }
}
