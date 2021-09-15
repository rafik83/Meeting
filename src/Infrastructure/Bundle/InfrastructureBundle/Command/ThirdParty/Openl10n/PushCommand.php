<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ThirdParty\Openl10n;

use Proximum\Vimeet\Application\ThirdParty\Openl10n;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushCommand extends Command
{
    public const NAME = 'vimeet:translations:push';

    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        parent::__construct(self::NAME);

        $this->commandBus = $commandBus;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Push the translations from the local files to the server')
            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'The locale id, "default" for the source, "all" for every locales found',
                ['default']
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new Openl10n\Push($input->getOption('locale'));

        /** @var Openl10n\PushResult $pushResult */
        $pushResult = $this->commandBus->handle($command);

        $output->writeln('<info>Added locales</info>');

        if (empty($pushResult->addedLocales)) {
            $output->writeln('<comment>none</comment>');
        }

        foreach ($pushResult->addedLocales as $addedLocale) {
            $output->writeln($addedLocale);
        }

        $output->writeln('<info>Unknown locales</info>');

        if (empty($pushResult->unknownLocales)) {
            $output->writeln('<comment>none</comment>');
        }

        foreach ($pushResult->unknownLocales as $unknownLocale) {
            $output->writeln($unknownLocale);
        }

        $output->writeln('<info>Created resources</info>');

        if (empty($pushResult->createdFiles)) {
            $output->writeln('<comment>none</comment>');
        }

        foreach ($pushResult->createdFiles as $createdFile) {
            $output->writeln($createdFile);
        }

        $output->writeln('<info>Uploaded files</info>');

        if (empty($pushResult->uploadedFiles)) {
            $output->writeln('<comment>none</comment>');
        }

        foreach ($pushResult->uploadedFiles as $uploadedFile) {
            $output->writeln($uploadedFile);
        }
    }
}
