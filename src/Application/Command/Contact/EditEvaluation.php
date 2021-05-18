<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use DateTimeInterface;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Sheet;

class EditEvaluation implements Command
{
    public Contact $contact;
    public ?int $evaluation;
    public Sheet $sheet;
    public DateTimeInterface $evaluatedAt;

    public function __construct(Contact $contact, ?int $evaluation, Sheet $sheet, DateTimeInterface $evaluatedAt)
    {
        $this->contact = $contact;
        $this->evaluation = $evaluation;
        $this->sheet = $sheet;
        $this->evaluatedAt = $evaluatedAt;
    }
}
