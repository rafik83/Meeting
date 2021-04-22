<?php

namespace Proximum\Vimeet\Application\Query\Participant;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Participant;

class CardViewQuery implements Query
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var bool
     */
    public $editable;

    /** @var bool */
    public $getCheckinStatus;

    public function __construct(
        Participant $participant,
        string $locale,
        bool $editable = false,
        $getCheckinStatus = false
    ) {
        $this->participant = $participant;
        $this->locale = $locale;
        $this->editable = $editable;
        $this->getCheckinStatus = $getCheckinStatus;
    }
}
