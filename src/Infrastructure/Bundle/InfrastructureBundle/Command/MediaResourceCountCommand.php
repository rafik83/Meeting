<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MediaResourceCountCommand extends Command
{
    public const NAME = 'vimeet:sheet:count-media-link';

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        EventRepositoryInterface $eventRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        parent::__construct(self::NAME);

        $this->sheetRepository = $sheetRepository;
        $this->eventRepository = $eventRepository;
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Count the number of media link on the sheets')
            ->addArgument(
                'event',
                InputArgument::REQUIRED,
                'Count for the given event'
            )
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'The type to count on'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventId = $input->getArgument('event');
        // 253
        $event = $this->eventRepository->getById($eventId);

        $typeId = $input->getArgument('type');
        // 1552
        $type = $this->typeRepository->getById($typeId);

        if ($type === null || $type->getEvent() !== $event) {
            return;
        }

        $sheets = $this->sheetRepository->getByTypes([$type]);

        $link = 0;
        foreach ($sheets as $sheet) {
            $data = $sheet->getData();

            // id to check
            // M2541Md657
            // M199fMfc3c

            if (isset($data['M2541Md657']) && is_array($data['M2541Md657'])) {
                foreach ($data['M2541Md657'] as $element) {
                    if (isset($element['path'])) {
                        ++$link;
                    }
                }
            }

            if (isset($data['M199fMfc3c']['medias']) && is_array($data['M199fMfc3c']['medias'])) {
                foreach ($data['M199fMfc3c']['medias'] as $element) {
                    if (isset($element['url'])) {
                        ++$link;
                    }
                }
            }
        }

        $output->writeln("The number of element is $link");
    }
}
