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

        $currentSettings = [];
        foreach ($event->getLocales() as $locale) {
            $currentSettings[$locale] = [
                'header' => $visioSettings->getHeader($locale),
                'endSound' => $visioSettings->getEndSound($locale),
                'endImage' => $visioSettings->getEndImage($locale),
            ];
        }

        foreach ($updateVisioSettings->localizedVisioSettings as $locale => $visioSettingsLocalized) {
            $visioHeaderImage = $this->handleFile(
                'header',
                'removeHeader',
                $currentSettings,
                $locale,
                $visioSettingsLocalized
            );
            $visioEndSound = $this->handleFile(
                'endSound',
                'removeEndSound',
                $currentSettings,
                $locale,
                $visioSettingsLocalized
            );
            $visioEndImage = $this->handleFile(
                'endImage',
                'removeEndImage',
                $currentSettings,
                $locale,
                $visioSettingsLocalized
            );

            $visioSettings->updateTranslation(
                $locale,
                $visioHeaderImage,
                $visioEndSound,
                $visioEndImage,
                $visioSettingsLocalized['endMessage']
            );
        }

        $this->visioSettingsRepository->update($visioSettings);
    }

    private function handleFile(
        string $variableName,
        string $removeVariableName,
        array &$currentSettings,
        string $locale,
        array &$data
    ): ?string {
        $currentFile = $currentSettings[$locale][$variableName] ?? null;
        $visioFile = $currentFile;

        if (true === $data[$removeVariableName]
            && null  !== $currentFile
        ) {
            $this->fileStorage->remove($currentFile);

            $currentFile = null;
            $visioFile = null;
        }

        if ($data[$variableName] instanceof UploadedFile) {
            if (null !== $currentFile) {
                $this->fileStorage->remove($currentFile);
            }

            $visioFile = $this->fileStorage->upload($data[$variableName]);
        }

        return $visioFile;
    }
}
