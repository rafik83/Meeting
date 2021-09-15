<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchRefuse;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchRefuseHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchPostRefuseCommand extends Command
{
    const NAME = 'vimeet:sheets:post-refuse';

    /** @var AdminRepositoryInterface */
    private $adminRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var PostBatchRefuseHandler */
    private $postBatchRefuseHandler;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param PostBatchRefuseHandler   $postBatchRefuseHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchRefuseHandler $postBatchRefuseHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository = $adminRepository;
        $this->sheetRepository = $sheetRepository;
        $this->postBatchRefuseHandler = $postBatchRefuseHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch post refuse sheet action')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetIds = explode(',', $input->getArgument('sheetIds'));
        $sheets = $this->sheetRepository->findByIds($sheetIds);
        $admin = $this->adminRepository->findById($input->getArgument('adminId'));

        if (null === $admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $this->postBatchRefuseHandler->handle(new PostBatchRefuse($sheets, $admin));
    }
}
