<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class CreateMessageHandler
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CreateHandler constructor.
     *
     * @param \Twig_Environment   $twig
     * @param TranslatorInterface $translator
     * @param \DateTimeInterface  $dateTime
     */
    public function __construct(
        \Twig_Environment $twig,
        TranslatorInterface $translator,
        \DateTimeInterface $dateTime
    ) {
        $this->twig       = $twig;
        $this->dateTime   = $dateTime;
        $this->translator = $translator;
    }

    /**
     * @param CreateMessage $command
     *
     * @return Message
     */
    public function handle(CreateMessage $command)
    {
        $message = new Message($command->event, $this->dateTime, $command->name);

        $emailContent = $this->twig->load($command->emailTemplate)->render();

        foreach ($command->sheets as $sheet) {
            $locale = $sheet->getOwnerLocale();

            if (!$message->hasTranslation($locale)) {
                $emailSubject = $this->translator->trans(
                    $command->subject,
                    [],
                    'mail',
                    $locale
                );

                $message->translate($locale, $emailSubject, $emailContent, $this->dateTime);
            }
        }

        return $message;
    }
}
