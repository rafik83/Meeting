<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleUpdateTranslationsCommand extends Command
{
    const NAME = 'vimeet:translations:schedule-update';

    /** @var BuildCreatorInterface */
    private $buildCreator;

    /** @var array */
    private $buildNames;

    public function __construct(BuildCreatorInterface $buildCreator, array $buildNames)
    {
        parent::__construct(self::NAME);

        $this->buildCreator = $buildCreator;
        $this->buildNames = $buildNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Schedule update translations')
            ->addArgument(
                'emailToNotify',
                InputArgument::REQUIRED,
                'Email of the admin to notify for completion of the task'
            )
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Locale for the email'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->buildNames as $buildName) {
            $this->buildCreator->create(
                $buildName,
                [
                    'EMAIL' => $input->getArgument('emailToNotify'),
                    'LOCALE' => $input->getArgument('locale'),
                ]
            );
            $output->writeln(sprintf('<info>Build "%s" is created.</info>', $buildName));
        }

        $output->writeln('<info>Translations update scheduled!</info>');
    }
}
