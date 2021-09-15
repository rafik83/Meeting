<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildEventGuidelineAssetCommand extends Command
{
    const NAME = 'vimeet:event:build-guideline-asset';

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var Generator */
    private $generator;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param Generator                $generator
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        Generator $generator
    ) {
        parent::__construct(self::NAME);

        $this->eventRepository = $eventRepository;
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Build guideline asset for the events')
            ->addOption(
                'event',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, the asset will be build only for the given event id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start building guideline assets');

        $eventId = $input->getOption('event');
        $events  = [];
        $event   = null;

        if (null !== $eventId) {
            $event = $this->eventRepository->getById($eventId);

            if (null === $event) {
                $output->writeln(
                    sprintf(
                        'The event for the id %s was not found, the guideline assets will not be built',
                        $eventId
                    )
                );
            } else {
                $output->writeln(
                    sprintf(
                        'Building guideline assets for the event %s with id %s',
                        $event->getTitle(),
                        $eventId
                    )
                );
            }
        } else {
            $events = $this->eventRepository->getAll();
        }

        if (null !== $event) {
            $this->buildAsset($output, $event);
        } elseif (!empty($events)) {
            foreach ($events as $event) {
                $this->buildAsset($output, $event);
            }
        }

        $output->writeln('End building guideline assets');

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Event           $event
     */
    private function buildAsset(OutputInterface $output, Event $event)
    {
        try {
            $assetPath = $this->generator->generate($event);
            $event->setAssetPath($assetPath);
            $this->eventRepository->set($event);

            $output->writeln(
                sprintf(
                    'Guideline assets built for the event %s with the id %s',
                    $event->getTitle(),
                    $event->getId())
            );
        } catch (GuidelineAssetBuildFailedException $ex) {
            $output->writeln(
                sprintf('Could not build the asset for the event %s with the id %s',
                    $event->getTitle(),
                    $event->getId())
            );
        }
    }
}
