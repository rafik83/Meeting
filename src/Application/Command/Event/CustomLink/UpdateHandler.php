<?php

namespace Proximum\Vimeet\Application\Command\Event\CustomLink;

use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;

class UpdateHandler
{
    private CustomLinkRepositoryInterface $customLinkRepository;

    public function __construct(CustomLinkRepositoryInterface $customLinkRepository)
    {
        $this->customLinkRepository = $customLinkRepository;
    }

    public function handle(Update $command)
    {
        $command->customLink->update(
            $command->translatedLabels,
            $command->types,
            $command->url,
            $command->iconName,
            $command->iconColor,
            $command->labelColor,
            $command->buttonColor,
            $command->priority
        );

        $this->customLinkRepository->set($command->customLink);
    }
}
