<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index;

use Proximum\Vimeet\Application\Command\Type\Index;
use Proximum\Vimeet\Application\Command\Type\IndexHandler;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsByTypesCommand extends Command
{
    const NAME = 'vimeet:type:index-sheets';

    /** @var IndexHandler */
    private $indexHandler;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /**
     * @param IndexHandler            $indexHandler
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(IndexHandler $indexHandler, TypeRepositoryInterface $typeRepository)
    {
        parent::__construct(self::NAME);

        $this->indexHandler = $indexHandler;
        $this->typeRepository = $typeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index Sheets by Type(s)')
            ->addArgument('typeIds', InputArgument::REQUIRED, 'Type ids separated by a comma')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $typeIds = explode(',', $input->getArgument('typeIds'));
        $types = $this->typeRepository->getByIds($typeIds);
        $this->indexHandler->handle(new Index($types));
    }
}
