<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Participant\ParticipantProduct;

use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetProductToParticipantWithPackageParticipantAndPlanningDisabledCommand extends Command
{
    protected static $defaultName = 'vimeet:participant:set-product';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantOfSheetWithPackageParticipantAndPlanningDisabled */
    private $participantOfSheetWithPackageParticipantAndPlanningDisabled;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantOfSheetWithPackageParticipantAndPlanningDisabled $participantOfSheetWithPackageParticipantAndPlanningDisabled
    ) {
        $this->eventRepository = $eventRepository;
        $this->participantRepository = $participantRepository;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $participantOfSheetWithPackageParticipantAndPlanningDisabled;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(
                'Set default participant product to participant with package participant and planning step disabled'
            )
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById($input->getArgument('eventId'));

        if (null === $event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        $participants = $this->participantRepository->findByEvent($event);

        $output->writeln($this->getDescription());
        $output->writeln(sprintf('Found %d participants for Event id %d', count($participants), $event->getId()));

        $participantsUpdated = 0;

        foreach ($participants as $participant) {
            $isUpdated = $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->handle($participant);
            $participantsUpdated += $isUpdated ? 1 : 0;
        }

        $output->writeln(sprintf('%d participants updated for Event id %d', $participantsUpdated, $event->getId()));
        $output->writeln('End of the process !');
    }
}
