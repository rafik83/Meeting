<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;

class AggregateHandler
{
    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var ZipArchiveAdapterInterface */
    private $zipArchiveAdapter;

    /** @var ZipRecordArchiveStorageInterface */
    private $zipRecordArchiveStorage;

    /** @var FinderAdapterInterface */
    private $finder;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        ZipArchiveAdapterInterface $zipArchiveAdapter,
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage,
        FinderAdapterInterface $finder
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->zipArchiveAdapter = $zipArchiveAdapter;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->finder = $finder;
    }

    public function handle(Aggregate $command): void
    {
        $recordArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($command->happening);

        if (count($recordArchives) > 1) {
            $files = [];
            $index = 1;

            // create temporary directory
            $tempDir = sys_get_temp_dir().'/'.uniqid();
            mkdir($tempDir);

            foreach ($recordArchives as $recordArchive) {
                $tempFilePath = sprintf('%s/webinar-%d-part%d.mp4', $tempDir, $command->happening->getId(), $index);
                dump($recordArchive->getPath().'::'.$tempFilePath);
                if ($recordArchive->getPath()) {
                    // todo do copy in an adapter
                    copy($recordArchive->getPath(), $tempFilePath);
                    $index++;
                }
            }
            $files = $this->finder->filesIn($tempDir);

            if (count($files) === 0) {
                throw new \RuntimeException('No archive found for webinar');
            }

            $zipFilePath = sprintf('%s/webinar-%d.zip', sys_get_temp_dir(), $command->happening->getId());
            $this->zipArchiveAdapter->zipFiles($files, $zipFilePath, $tempDir);

            $zipFile = new \SplFileInfo($zipFilePath);
            dump($zipFile);

            $this->zipRecordArchiveStorage->upload($zipFile, 'multiple-archives/' . $zipFile->getFilename());

            unlink($zipFile->getRealPath());
            $filesystem = new Filesystem();
            $filesystem->remove($tempDir);
        }
    }
}
