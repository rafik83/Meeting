<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EditCommentHandler
{
    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function handle(EditComment $query): void
    {
        $query->contact->setComment($query->comment);
        $this->contactRepository->set($query->contact);
    }
}
