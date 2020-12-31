<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index;

use Proximum\Vimeet\Application\Command\Event\Index;
use Proximum\Vimeet\Application\Command\Event\IndexHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexInCatalogSheetsByEventCommand extends Command
{
    const NAME = 'vimeet:event:index-in-catalog-sheets';

    /** @var IndexHandler */
    private $indexHandler;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param IndexHandler             $indexHandler
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(IndexHandler $indexHandler, EventRepositoryInterface $eventRepository)
    {
        parent::__construct(self::NAME);

        $this->indexHandler    = $indexHandler;
        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index in catalog Sheets by Event')
            ->addArgument('eventId', InputArgument::REQUIRED, 'Event id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $event = $this->eventRepository->getById(
            $input->getArgument('eventId')
        );

        if (null === $event) {
            throw new \Exception('Event not found.');
        }

        $this->indexHandler->handle(new Index($event));
    }
}
