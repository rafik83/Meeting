<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class TipTranslationViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var Type */
    public $type;

    /** @var string */
    public $context;

    /** @var string */
    public $locale;

    public function __construct(Sheet $sheet, User $user, string $context, string $locale)
    {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->event = $sheet->getEvent();
        $this->type = $sheet->getType();
        $this->context = $context;
        $this->locale = $locale;
    }
}
