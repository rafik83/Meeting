<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Meeting\Request;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipateToRequestEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssignAllParticipantsToRequestCommand extends Command
{
    public const NAME = 'vimeet:meeting-request:assign-all-participants';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        TypeRepositoryInterface $typeRepository,
        RequestRepositoryInterface $requestRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->typeRepository = $typeRepository;
        $this->requestRepository = $requestRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Assign all the participants on the request of the given type')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event ID')
            ->addArgument('typeId', InputArgument::REQUIRED, 'Type ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $type = $this->typeRepository->getById($input->getArgument('typeId'));

        if (!$type instanceof Type || $type->getEvent() !== $event) {
            throw new \InvalidArgumentException('Type not found.');
        }

        $participants = [];
        $requests = $this->requestRepository->getApprovedByType($event, $type);

        foreach ($requests as $request) {
            if (count($request->getFromParticipantsArray()) !== $request->getFromSheet()->countParticipants()) {
                foreach ($request->getFromSheet()->getParticipantsArray() as $fromParticipant) {
                    if (!$request->hasFromParticipant($fromParticipant)) {
                        $request->addFromParticipant($fromParticipant);

                        $participants[$fromParticipant->getId()] = $fromParticipant;
                    }
                }
            }

            if (count($request->getToParticipantsArray()) !== $request->getToSheet()->countParticipants()) {
                foreach ($request->getToSheet()->getParticipantsArray() as $toParticipant) {
                    if (!$request->hasToParticipant($toParticipant)) {
                        $request->addToParticipant($toParticipant);

                        $participants[$toParticipant->getId()] = $toParticipant;
                    }
                }
            }

            $this->requestRepository->update($request);
        }

        foreach ($participants as $participant) {
            $this->eventDispatcher->dispatch(Events::REQUEST_PARTICIPATE, new ParticipateToRequestEvent($participant));
        }
    }
}
