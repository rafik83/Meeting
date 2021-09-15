<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Batch;

use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchCatalogHandler;
use Proximum\Vimeet\Domain\Repository\AdminRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BatchCatalogCommand extends Command
{
    const NAME = 'vimeet:sheets:catalog';

    /**
     * @var AdminRepositoryInterface
     */
    private $adminRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var PostBatchCatalogHandler
     */
    private $postBatchCatalogHandler;

    /**
     * @param AdminRepositoryInterface $adminRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param PostBatchCatalogHandler  $postBatchCatalogHandler
     */
    public function __construct(
        AdminRepositoryInterface $adminRepository,
        SheetRepositoryInterface $sheetRepository,
        PostBatchCatalogHandler $postBatchCatalogHandler
    ) {
        parent::__construct(self::NAME);

        $this->adminRepository         = $adminRepository;
        $this->sheetRepository         = $sheetRepository;
        $this->postBatchCatalogHandler = $postBatchCatalogHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Batch catalog sheet action')
            ->addArgument('sheetIds', InputArgument::REQUIRED, 'Sheet ids separated by a comma')
            ->addArgument('adminId', InputArgument::REQUIRED, 'Admin id')
            ->addArgument('state', InputArgument::REQUIRED, 'Add catalog state');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sheetIds = explode(',', $input->getArgument('sheetIds'));
        $state    = $input->getArgument('state');
        $sheets   = $this->sheetRepository->findByIds($sheetIds);
        $admin    = $this->adminRepository->findById($input->getArgument('adminId'));

        if (null === $admin) {
            throw new \InvalidArgumentException('Admin not found.');
        }

        $this->postBatchCatalogHandler->handle(
            new PostBatchCatalog($sheets, $admin, $state)
        );
    }
}
