<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchEnableDisable;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchEnableDisableHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchEnableDisableCommand extends Command
{
    const NAME = 'vimeet:sheets:enable-disable';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var PostBatchEnableDisableHandler
     */
    private $postBatchEnableDisableHandler;

    /**
     * @param AdminRepositoryInterface      $adminRepository
     * @param SheetRepositoryInterface      $sheetRepository
     * @param PostBatchEnableDisableHandler $postBatchEnableDisableHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchEnableDisableHandler $postBatchEnableDisableHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository               = $adminRepository;
        $this->sheetRepository               = $sheetRepository;
        $this->postBatchEnableDisableHandler = $postBatchEnableDisableHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch enable disable sheet action')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('state', InputArgument::REQUIRED, 'Batch state');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetIds = explode(',', $input->getArgument('sheetIds'));
        $sheets   = $this->sheetRepository->findByIds($sheetIds);
        $state    = $input->getArgument('state');
        $admin    = $this->adminRepository->findById($input->getArgument('adminId'));

        if (null === $admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $this->postBatchEnableDisableHandler->handle(
            new PostBatchEnableDisable($sheets, $sheetIds, $admin, $state)
        );
    }
}
