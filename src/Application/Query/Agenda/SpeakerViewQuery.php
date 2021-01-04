<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Happening;

class SpeakerViewQuery
{
    /**
     * @var Happening
     */
    public $happening;

    /**
     * @var string
     */
    public $locale;

    /**
     * SpeakerViewQuery constructor.
     *
     * @param Happening $happening
     * @param string    $locale
     */
    public function __construct(Happening $happening, $locale)
    {
        $this->happening = $happening;
        $this->locale    = $locale;
    }
}
