<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\UuidGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\VideoStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoStorageAdapter implements VideoStorageInterface
{
    /** @var GoogleCloudStorageAdapter */
    private $googleCloudStorageAdapter;

    /** @var string */
    private $googleCloudStorageUri;

    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        GoogleCloudStorageAdapter $googleCloudStorageAdapter,
        string $googleCloudStorageUri,
        UuidGeneratorInterface $uuidGenerator,
        FileStorageInterface $fileStorage
    ) {
        $this->googleCloudStorageAdapter = $googleCloudStorageAdapter;
        $this->googleCloudStorageUri = $googleCloudStorageUri;
        $this->uuidGenerator = $uuidGenerator;
        $this->fileStorage = $fileStorage;
    }

    public function upload(Event $event, $file): ?string
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        $relativePath = sprintf(
            '/%s/%d/%s.%s',
            'sheet-video',
            $event->getId(),
            $this->uuidGenerator->generate(),
            $file->guessExtension()
        );

        $this->googleCloudStorageAdapter->create(
            $this->fileStorage->getContents($file),
            $relativePath
        );

        return sprintf('%s%s', $this->googleCloudStorageUri, $relativePath);
    }

    public function remove($path): void
    {
        if (empty($path)) {
            return;
        }

        $relativePath = str_replace(
            $this->googleCloudStorageUri,
            '',
            $path
        );

        $this->googleCloudStorageAdapter->delete($relativePath);
    }
}
