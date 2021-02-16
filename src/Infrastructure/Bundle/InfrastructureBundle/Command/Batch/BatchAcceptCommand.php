<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchAccept;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchAcceptHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchAcceptCommand extends Command
{
    const NAME = 'vimeet:sheets:accept';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var PostBatchAcceptHandler
     */
    private $postBatchAcceptHandler;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param PostBatchAcceptHandler   $postBatchAcceptHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchAcceptHandler $postBatchAcceptHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository        = $adminRepository;
        $this->postBatchAcceptHandler = $postBatchAcceptHandler;
        $this->sheetRepository        = $sheetRepository;
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

        $this->postBatchAcceptHandler->handle(new PostBatchAccept($sheets, $admin));
    }
}
