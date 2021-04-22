<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index;

use Proximum\Vimeet\Application\Command\Template\Registration\Index;
use Proximum\Vimeet\Application\Command\Template\Registration\IndexHandler;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexSheetsByRegistrationTemplateCommand extends Command
{
    const NAME = 'vimeet:registration-template:index-sheets';

    /** @var IndexHandler */
    private $indexHandler;

    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /**
     * @param IndexHandler                            $indexHandler
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     */
    public function __construct(
        IndexHandler $indexHandler,
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository
    ) {
        parent::__construct(self::NAME);

        $this->indexHandler                   = $indexHandler;
        $this->registrationTemplateRepository = $registrationTemplateRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Index Sheets by RegistrationTemplate')
            ->addArgument('registrationTemplateId', InputArgument::REQUIRED, 'RegistrationTemplate id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $registrationTemplate = $this->registrationTemplateRepository->findById(
            $input->getArgument('registrationTemplateId')
        );

        if (null === $registrationTemplate) {
            throw new \Exception('RegistrationTemplate not found.');
        }

        $this->indexHandler->handle(new Index($registrationTemplate));

        return 0;
    }
}
