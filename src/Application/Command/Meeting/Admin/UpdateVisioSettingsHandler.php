<?php


namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateVisioSettingsHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->eventRepository = $eventRepository;
        $this->fileStorage = $fileStorage;
    }

    public function handle(UpdateVisioSettings $updateVisioSettings): void
    {
        $event = $updateVisioSettings->event;

        $currentHeaders = [];
        foreach ($event->getLocales() as $locale) {
            $currentHeaders[$locale] = $event->getLocalizedVisioHeader($locale);
        }

        foreach ($updateVisioSettings->localizedVisioHeaders as $locale => $header) {
            $currentVisioHeaderImage = $currentHeaders[$locale] ?? null;
            $visioHeaderImage = $currentVisioHeaderImage;

            if (true === $header['removeHeader']
                && null  !== $currentVisioHeaderImage
            ) {
                $this->fileStorage->remove($currentVisioHeaderImage);

                $currentVisioHeaderImage = null;
                $visioHeaderImage = null;
            }

            if ($header['header'] instanceof UploadedFile) {
                if (null !== $currentVisioHeaderImage) {
                    $this->fileStorage->remove($currentVisioHeaderImage);
                }

                $visioHeaderImage = $this->fileStorage->upload($header['header']);
            }

            $event->updateLocalizedVisioHeader(
                $locale,
                $visioHeaderImage
            );
        }

        $this->eventRepository->set($event);
    }
}
