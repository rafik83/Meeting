<?php


namespace Proximum\Vimeet\Application\Command\Visio;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateVisioSettingsHandler
{
    /** @var VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        VisioSettingsRepositoryInterface $visioSettingsRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->visioSettingsRepository = $visioSettingsRepository;
        $this->fileStorage = $fileStorage;
    }

    public function handle(UpdateVisioSettings $updateVisioSettings): void
    {
        $event = $updateVisioSettings->event;
        $visioSettings = $updateVisioSettings->visioSettings;

        $currentHeaders = [];
        foreach ($event->getLocales() as $locale) {
            $currentHeaders[$locale] = $visioSettings->getHeader($locale);
        }

        foreach ($updateVisioSettings->localizedVisioSettings as $locale => $visioSettingsLocalized) {
            $currentVisioHeaderImage = $currentHeaders[$locale] ?? null;
            $visioHeaderImage = $currentVisioHeaderImage;

            if (true === $visioSettingsLocalized['removeHeader']
                && null  !== $currentVisioHeaderImage
            ) {
                $this->fileStorage->remove($currentVisioHeaderImage);

                $currentVisioHeaderImage = null;
                $visioHeaderImage = null;
            }

            if ($visioSettingsLocalized['header'] instanceof UploadedFile) {
                if (null !== $currentVisioHeaderImage) {
                    $this->fileStorage->remove($currentVisioHeaderImage);
                }

                $visioHeaderImage = $this->fileStorage->upload($visioSettingsLocalized['header']);
            }

            $visioSettings->updateTranslation(
                $locale,
                $visioHeaderImage
            );
        }

        $this->visioSettingsRepository->update($visioSettings);
    }
}
