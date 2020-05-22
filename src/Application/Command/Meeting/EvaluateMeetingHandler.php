<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EvaluateMeetingHandler
{
    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(ContactRepositoryInterface $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function handle(EvaluateMeeting $command): void
    {

    }
}
