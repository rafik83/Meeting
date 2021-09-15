<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event;

use Proximum\Vimeet\Application\Command\Event\Index\IndexHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexFromScratchCommand extends Command
{
    const NAME = 'vimeet:elasticsearch:index';

    /** @var IndexHandler */
    private $indexHandler;

    /**
     * @param IndexHandler $indexHandler
     */
    public function __construct(IndexHandler $indexHandler)
    {
        parent::__construct(self::NAME);

        $this->indexHandler = $indexHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index from scratch elasticsearch')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting by reseting elasticsearch');
        $resetCommand = $this->getApplication()->find('fos:elastica:reset');
        $resetCommand->run(new ArrayInput(['--no-debug']), $output);
        $output->writeln('Reset done');

        $output->writeln('Preparing jobs to reindex all the events');
        $this->indexHandler->handle();
        $output->writeln('Jobs are pending');

        $output->writeln('You can go back to your normal activities');
    }
}
