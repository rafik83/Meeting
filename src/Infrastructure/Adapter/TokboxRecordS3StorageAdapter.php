<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class TokboxRecordS3StorageAdapter
{
    /** @var string */
    private $s3Key;

    /** @var string */
    private $s3Secret;

    /** @var string */
    private $s3BucketName;

    /** @var string */
    private $tokboxApiKey;

    /** @var string */
    private $s3Region;

    public function __construct(
        string $s3Key,
        string $s3Secret,
        string $s3BucketName,
        string $s3Region,
        string $tokboxApiKey
    ) {
        $this->s3Key = $s3Key;
        $this->s3Secret = $s3Secret;
        $this->s3BucketName = $s3BucketName;
        $this->tokboxApiKey = $tokboxApiKey;
        $this->s3Region = $s3Region;
    }

    private function init(): Filesystem
    {
        $storageClient = new S3Client([
            'credentials' => [
                'key'    => $this->s3Key,
                'secret' => $this->s3Secret
            ],
            'region' => $this->s3Region,
            'version' => 'latest',
        ]);


        $adapter = new AwsS3Adapter($storageClient, $this->s3BucketName);

        return new Filesystem($adapter);
    }

    public function getFile(string $archiveId)
    {
        $flySystem = $this->init();

        return $flySystem->read($this->getPath($archiveId));
    }

    private function getPath($archiveId): string
    {
        return sprintf('/%s/%s/archive.mp4', $this->tokboxApiKey, $archiveId);
    }

    public function hasFile(string $archiveId): bool
    {
        $flySystem = $this->init();

        return $flySystem->has($this->getPath($archiveId));
    }
}
