<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\Process;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ProcessHandler;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCampaignCommand extends Command
{
    public const NAME = 'vimeet:campaign:send';

    /**
     * @var CampaignRepositoryInterface
     */
    private $repository;

    /**
     * @var ProcessHandler
     */
    private $handler;

    /**
     * SendCampaignCommand constructor.
     *
     * @param CampaignRepositoryInterface $repository
     * @param ProcessHandler              $handler
     */
    public function __construct(CampaignRepositoryInterface $repository, ProcessHandler $handler)
    {
        parent::__construct(self::NAME);

        $this->repository = $repository;
        $this->handler    = $handler;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Send campaign')
            ->addArgument('id', InputArgument::REQUIRED, 'Campaign id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $campaign = $this->repository->getById($input->getArgument('id'));

        if (null === $campaign) {
            throw new \Exception('Campaign not found.');
        }

        $this->handler->handle(new Process($campaign));

        return 0;
    }
}
