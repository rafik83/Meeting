<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Export;

use Proximum\Vimeet\Application\Command\Sheet\Export\Export;
use Proximum\Vimeet\Application\Command\Sheet\Export\ExportHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportSheetCommand extends Command
{
    public const NAME = 'vimeet:sheet:export';

    /** @var ExportHandler */
    private $exportHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    public function __construct(
        ExportHandler $exportHandler,
        EventRepositoryInterface $eventRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        AdminRepositoryInterface $adminRepository
    ) {
        parent::__construct(self::NAME);

        $this->exportHandler = $exportHandler;
        $this->extraDataRepository = $extraDataRepository;
        $this->adminRepository = $adminRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Export the sheets in a csv file and notify the admin who requested it.')
            ->addOption('eventId', null, InputOption::VALUE_REQUIRED, 'Event id')
            ->addOption('extraDataWithSheetIds', null, InputOption::VALUE_REQUIRED, 'id of the Extra Data that contains the ids of the sheet')
            ->addOption('adminId', null, InputOption::VALUE_REQUIRED, 'admin to notify at the end of the command')
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'locale for the export')
            ->addOption('displayNomenclatureIds', null, InputOption::VALUE_REQUIRED, 'Display nomenclature ids in the export')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getOption('eventId')
            || null === $input->getOption('extraDataWithSheetIds')
            || null === $input->getOption('adminId')
            || null === $input->getOption('locale')
            || null === $input->getOption('displayNomenclatureIds')
        ) {
            $output->writeln(
                '<error>The eventId, extraDataWithSheetIds, adminId and locale options are mandatory and can not be null</error>'
            );

            throw new \InvalidArgumentException(
                sprintf(
                    'The eventId, extraDataWithSheetIds, adminId, locale and displayNomenclatureIds options are mandatory and can not be null, arguments passed: eventId=%s extraDataWithSheetIds=%s adminId=%s locale=%s displayNomenclatureIds=%s',
                    $input->getOption('eventId'),
                    $input->getOption('extraDataWithSheetIds'),
                    $input->getOption('adminId'),
                    $input->getOption('locale'),
                    $input->getOption('displayNomenclatureIds')
                )
            );
        }

        $sheetIdsExtraDataId = (int) $input->getOption('extraDataWithSheetIds');
        $sheetIdsExtraData = $this->extraDataRepository->findById($sheetIdsExtraDataId);

        $event = $this->eventRepository->getById($input->getOption('eventId'));
        $admin = $this->adminRepository->findById($input->getOption('adminId'));

        if (!$sheetIdsExtraData instanceof ExtraData) {
            throw new \InvalidArgumentException('The sheetIdsExtraData does not exist');
        }

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('The admin does not exist');
        }

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('The event does not exist');
        }

        $command = new Export(
            $event,
            $admin,
            $input->getOption('locale'),
            explode(',', $sheetIdsExtraData->getValue()),
            'true' === $input->getOption('displayNomenclatureIds')
        );

        $this->exportHandler->handle($command);
    }
}
