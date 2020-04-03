<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PullTranslationsCommand extends UpdateTranslationsCommand
{
    const NAME = 'vimeet:translations:pull';

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(self::NAME)
            ->setDescription('Pull translations from openl10n')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->pull($input, $output);

        array_map('unlink', glob(sprintf('%s/translations/*', $this->kernelCacheDir)));

        return 0;
    }
}
