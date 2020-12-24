<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDraft;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchDraftHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchDraftCommand extends Command
{
    const NAME = 'vimeet:sheets:draft';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var PostBatchDraftHandler
     */
    private $postBatchDraftHandler;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param PostBatchDraftHandler    $postBatchDraftHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchDraftHandler $postBatchDraftHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository       = $adminRepository;
        $this->sheetRepository       = $sheetRepository;
        $this->postBatchDraftHandler = $postBatchDraftHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch draft sheet action')
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

        $this->postBatchDraftHandler->handle(
            new PostBatchDraft($sheets, $admin)
        );
    }
}
