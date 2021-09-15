<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSheetsTitleCommand extends Command
{
    const NAME = 'vimeet:sheets:title:update';

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * UpdateSheetsTitleCommand constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param SheetInfoGuesserCache    $sheetInfoGuesserCache
     * @param EntityManager            $entityManager
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetInfoGuesserCache $sheetInfoGuesserCache,
        EntityManager $entityManager
    ) {
        parent::__construct(self::NAME);

        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
        $this->eventRepository       = $eventRepository;
        $this->entityManager         = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Update sheets title')
            ->addArgument('eventId', InputArgument::OPTIONAL, 'Event id');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetsIterableResult = [];
        $eventId              = $input->getArgument('eventId');

        if (null !== $eventId) {
            $event = $this->eventRepository->getById($eventId);
            if (null !== $event) {
                $sheetsIterableResult = $this->entityManager->createQueryBuilder()
                    ->select('sheet')
                    ->from(Sheet::class, 'sheet')
                    ->where('sheet.event = :event')
                    ->setParameter('event', $event)
                    ->getQuery()->iterate();
            }
        } else {
            $sheetsIterableResult = $this->entityManager->createQueryBuilder()
                ->select('sheet')
                ->from(Sheet::class, 'sheet')
                ->getQuery()->iterate();
        }

        $batchSize        = 500;
        $i                = 0;
        $progress         = new ProgressBar($output);
        $sheetToBeUpdated = [];

        foreach ($sheetsIterableResult as $row) {
            /** @var Sheet $sheet */
            $sheet              = $row[0];
            $sheetTitle         = $this->sheetInfoGuesserCache->guessSheetTitle($sheet);
            $sheetToBeUpdated[] = $sheet->setTitle($sheetTitle);

            if (0 === ($i % $batchSize)) {
                $this->entityManager->flush($sheetToBeUpdated);
                $this->entityManager->clear();
                $sheetToBeUpdated = [];
            }

            ++$i;
            $progress->advance();
        }

        $progress->finish();
    }
}
