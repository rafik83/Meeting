<?php

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class CreateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(TipRepositoryInterface $tipRepository, \DateTimeInterface $dateTime)
    {
        $this->tipRepository = $tipRepository;
        $this->dateTime      = $dateTime;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $tip = new Tip(
            $command->title,
            null,
            $command->onMeetingManagement,
            $command->onCatalog,
            $command->onPrintPlanning,
            $command->onSheet,
            $command->onAgenda,
            $command->onPackage,
            $command->onContacts,
            $command->onProgram,
            $command->onConfirmationPhone,
            $command->onNetworking,
            $this->dateTime
        );

        foreach ($command->translations as $translation) {
            $tip->translate($translation['locale'], $translation['title'], $translation['content'], $this->dateTime);
        }

        $this->tipRepository->add($tip);
    }
}
