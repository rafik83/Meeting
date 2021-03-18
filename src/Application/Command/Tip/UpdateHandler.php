<?php

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * UpdateHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(TipRepositoryInterface $tipRepository, \DateTimeInterface $dateTime)
    {
        $this->tipRepository = $tipRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        foreach ($command->tip->getTranslations() as $translation) {
            foreach ($command->translations as $commandTranslation) {
                if (!isset($commandTranslation[$translation->getLocale()])) {
                    $command->tip->removeTranslation($translation->getLocale());
                    $this->tipRepository->removeTranslation($translation);
                }
            }
        }

        foreach ($command->translations as $translation) {
            $command->tip->translate(
                $translation['locale'],
                $translation['title'],
                $translation['content'],
                $this->dateTime
            );
        }

        $this->tipRepository->set(
            $command->tip->update(
                $command->title,
                $command->onMeetingManagement,
                $command->onCatalog,
                $command->onPrintPlanning,
                $command->onSheet,
                $command->onAgenda,
                $command->onPackage,
                $command->onContacts,
                $command->onProgram,
                $command->onConfirmationPhone,
                $command->onNetworking
            )
        );
    }
}
