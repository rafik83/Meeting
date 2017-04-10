<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Repository\Messaging\MessageRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(MessageRepositoryInterface $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $message = $command->getMessage();

        foreach ($message->getTranslations() as $locale => $translation) {
            // Remove translation if it was deleted
            if (!isset($command->translations[$locale])) {
                $this->messageRepository->removeTranslation($translation);
                $message->translations->remove($locale);
            }
        }

        foreach ($command->translations as $locale => $translation) {
            // Remove translation if locale was updated
            if ($message->translations->containsKey($locale)) {
                $this->messageRepository->removeTranslation($message->getTranslation($locale));
                $message->translations->remove($locale);
            }

            $message->translate(
                $translation['locale'],
                $translation['subject'],
                $translation['content']
            );
        }

        $this->messageRepository->set($message->update($command->name));
    }
}
