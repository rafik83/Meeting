<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use DateTimeInterface;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Contact;

class EditEvaluation implements Command
{
    public Contact $contact;
    public ?int $evaluation;
    public DateTimeInterface $evaluatedAt;

    public function __construct(Contact $contact, ?int $evaluation, DateTimeInterface $evaluatedAt)
    {
        $this->contact = $contact;
        $this->evaluation = $evaluation;
        $this->evaluatedAt = $evaluatedAt;
    }
}
