<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EditEvaluationHandler
{
    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function handle(EditEvaluation $query): void
    {
        $query->contact->setEvaluation($query->evaluation);
        $this->contactRepository->set($query->contact);
    }
}
