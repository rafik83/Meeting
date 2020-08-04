<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\File\FileTemporary;
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

    public function handle(Aggregate $command): FileTemporary
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

            $fileName = sprintf('webinar-%d.zip', $command->happening->getId());
            $zipFilePath = sprintf('%s/%s', sys_get_temp_dir(), $fileName);
            $this->zipArchiveAdapter->zipFiles($files, $zipFilePath, '');

            $this->zipRecordArchiveStorage->upload($zipFilePath, 'multiple-archives/' . $fileName);

            $filesystem = new Filesystem();
            $filesystem->remove($tempDir);

            return new FileTemporary($zipFilePath, $fileName);
        }
    }
}
