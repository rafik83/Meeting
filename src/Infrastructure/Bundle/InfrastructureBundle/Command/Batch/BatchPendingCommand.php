<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchPending;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchPendingHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchPendingCommand extends Command
{
    const NAME = 'vimeet:sheets:pending';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var PostBatchPendingHandler
     */
    private $postBatchPendingHandler;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param PostBatchPendingHandler  $postBatchPendingHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchPendingHandler $postBatchPendingHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository         = $adminRepository;
        $this->sheetRepository         = $sheetRepository;
        $this->postBatchPendingHandler = $postBatchPendingHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch accept sheet action')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetIds = explode(',', $input->getArgument('sheetIds'));
        $sheets   = $this->sheetRepository->findByIds($sheetIds);
        $admin    = $this->adminRepository->findById($input->getArgument('adminId'));

        if (null === $admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $this->postBatchPendingHandler->handle(new PostBatchPending($sheets, $admin));
    }
}
