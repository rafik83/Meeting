<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare;

use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;

abstract class AbstractPrepareMailService
{
    /** @var MessageRepositoryInterface */
    protected $messageRepository;

    /** @var SubstitutionHandler */
    protected $substitutionHandler;

    /** @var EventSender */
    protected $eventSenderGuesser;

    /** @var ParticipantMailViewQueryHandler */
    protected $participantMailViewQueryHandler;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        SubstitutionHandler $substitutionHandler,
        EventSender $eventSenderGuesser,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler
    ) {
        $this->messageRepository = $messageRepository;
        $this->substitutionHandler = $substitutionHandler;
        $this->eventSenderGuesser = $eventSenderGuesser;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
    }
}
