<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation;

use Proximum\Vimeet\Application\ThirdParty\Openl10n;
use Proximum\Vimeet\Infrastructure\Adapter\CommandBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTranslationsCommand extends Command
{
    const NAME = 'vimeet:translations:update';

    /** @var CommandBus */
    protected $commandBus;

    /** @var string */
    protected $kernelCacheDir;

    public function __construct(CommandBus $commandBus, string $kernelCacheDir)
    {
        parent::__construct(self::NAME);
        $this->commandBus = $commandBus;
        $this->kernelCacheDir = $kernelCacheDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Update translations from openl10n')
            ->addArgument(
                'emailToNotify',
                InputArgument::OPTIONAL,
                'Email of the admin to notify for completion of the task'
            )
            ->addArgument(
                'locale',
                InputArgument::OPTIONAL,
                'Locale for the email'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->push($input, $output);

        $this->pull($input, $output);

        array_map('unlink', glob(sprintf('%s/translations/*', $this->kernelCacheDir)));

        return 0;
    }

    protected function push(InputInterface $input, OutputInterface $output): void
    {
        $command = new Openl10n\Push();

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

    protected function pull(InputInterface $input, OutputInterface $output): void
    {
        $command = new Openl10n\Pull($input->getArgument('emailToNotify'), $input->getArgument('locale'));

        /** @var Openl10n\PullResult $pullResult */
        $pullResult = $this->commandBus->handle($command);

        $output->writeln('<info>Skipped files</info>');

        if (empty($pullResult->skippedFiles)) {
            $output->writeln('<comment>none</comment>');
        }

        foreach ($pullResult->skippedFiles as $skippedFile) {
            $output->writeln($skippedFile);
        }

        $output->writeln('<info>Downloaded files</info>');

        foreach ($pullResult->downloadedFiles as $downloadedFile) {
            $output->writeln($downloadedFile);
        }

        if (empty($pullResult->downloadedFiles)) {
            $output->writeln('<comment>none</comment>');
        }
    }
}
