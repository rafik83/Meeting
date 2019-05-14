<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Contact;

class EditEvaluation implements Command
{
    /** @var Contact */
    public $contact;

    /** @var int|null */
    public $evaluation;

    public function __construct(Contact $contact, ?int $evaluation)
    {
        $this->contact = $contact;
        $this->evaluation = $evaluation;
    }
}
