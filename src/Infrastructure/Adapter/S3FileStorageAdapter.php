<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Proximum\Vimeet\Application\Adapter\RemoteFileStorageInterface;

class S3FileStorageAdapter implements RemoteFileStorageInterface
{
    /** @var Filesystem */
    private $fileSystemAdapter;

    /** @var S3ClientInterface */
    private $client;

    /** @var string */
    private $bucket;

    /** @var AbstractAdapter */
    private $adapter;

    public function __construct(
        S3Client $client,
        string $bucket,
        string $prefix
    ) {
        $adapter = new AwsS3Adapter($client, $bucket, $prefix);
        $this->client = $client;
        $this->bucket = $bucket;
        $this->adapter = $adapter;
        $this->fileSystemAdapter = new Filesystem($adapter, ['visibility' => 'public']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function upload(\SplFileInfo $file, ?string $remotePath = null): void
    {
        $resolvedRemotePath = $remotePath ? $remotePath : $file->getFilename();

        if ($this->fileSystemAdapter->has($resolvedRemotePath)) {
            throw new \RuntimeException('Remote file already exists');
        }

        $this->fileSystemAdapter->writeStream($resolvedRemotePath, fopen($file->getRealPath(), 'r'));

        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $this->adapter->applyPathPrefix($resolvedRemotePath),
        ]);
        // dump((string) ($this->client->createPresignedRequest($command, '+1 minute'))->getUri());
        // die;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function download(string $remoteFilePath, string $localPath): string
    {
        if (!$this->fileSystemAdapter->has($remoteFilePath)) {
            throw new \RuntimeException('Remote file does not exist');
        }

        stream_copy_to_stream($this->fileSystemAdapter->readStream($remoteFilePath), fopen($localPath, 'w'));

        return $localPath;
    }
}
