<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class AddHandler
{
    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(ContactRepositoryInterface $contactRepository, \DateTimeInterface $dateTime)
    {
        $this->contactRepository = $contactRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Add $add): void
    {
        if ($add->user->getId() === $add->contact->getId()) {
            return;
        }

        $contact = new Contact($add->event, $add->user, $add->contact, $this->dateTime, $add->origin);

        if (null !== $this->contactRepository->find($contact)) {
            return;
        }

        $this->contactRepository->add($contact);
    }
}
