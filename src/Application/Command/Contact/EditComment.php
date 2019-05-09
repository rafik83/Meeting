<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Contact;

class EditComment implements Command
{
    /** @var Contact */
    public $contact;

    /** @var string|null */
    public $comment;

    public function __construct(Contact $contact, ?string $comment)
    {
        $this->contact = $contact;
        $this->comment = $comment;
    }
}
