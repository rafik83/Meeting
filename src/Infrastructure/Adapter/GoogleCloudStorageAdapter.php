<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Google\Cloud\Storage\StorageClient;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class GoogleCloudStorageAdapter
{
    /** @var array */
    private $config;

    /** @var string */
    private $projectId;

    /** @var string */
    private $bucketName;

    /** @var string */
    private $type;

    /** @var string */
    private $privateKeyId;

    /** @var string */
    private $privateKey;

    /** @var string */
    private $clientEmail;

    /** @var string */
    private $clientId;

    /** @var string */
    private $authUri;

    /** @var string */
    private $tokenUri;

    /** @var string */
    private $authProviderX509CertUrl;

    /** @var string */
    private $clientX509CertUrl;

    public function __construct(
        array $googleCloudStorageConfig
    ) {
        $this->config = $googleCloudStorageConfig;
        $this->projectId = $this->config['project_id'];
        $this->bucketName = $this->config['bucket_name'];
        $this->type = $this->config['type'];
        $this->privateKeyId = $this->config['private_key_id'];
        $this->privateKey = $this->config['private_key'];
        $this->clientEmail = $this->config['client_email'];
        $this->clientId = $this->config['client_id'];
        $this->authUri = $this->config['auth_uri'];
        $this->tokenUri = $this->config['token_uri'];
        $this->authProviderX509CertUrl = $this->config['auth_provider_x509_cert_url'];
        $this->clientX509CertUrl = $this->config['client_x509_cert_url'];
    }

    private function init(): Filesystem
    {
        $storageClient = new StorageClient([
            'projectId' => $this->projectId,
            'keyFile' => [
                'type' => $this->type,
                'private_key_id' => $this->privateKeyId,
                'private_key' => $this->privateKey,
                'client_email' => $this->clientEmail,
                'client_id' => $this->clientId,
                'auth_uri' => $this->authUri,
                'token_uri' => $this->tokenUri,
                'auth_provider_x509_cert_url' => $this->authProviderX509CertUrl,
                'client_x509_cert_url' => $this->clientX509CertUrl,
            ],
        ]);

        $bucket = $storageClient->bucket($this->bucketName);

        $adapter = new GoogleStorageAdapter($storageClient, $bucket);

        return new Filesystem($adapter);
    }

    public function create(string $content, string $path): void
    {
        $flySystem = $this->init();

        $flySystem->write($path, $content);
    }

    public function upload(string $localFile, string $remotePath): void
    {
        $flySystem = $this->init();

        $fh = fopen($localFile, 'r');
        if (!$flySystem->writeStream($remotePath, $fh)) {
            throw new \RunTimeException(sprintf('File [%s] upload error', $remotePath));
        }
    }

    public function download(string $remotePath, $localPath): bool
    {
        $flySystem = $this->init();

        if (!$flySystem->has($remotePath)) {
            return false;
        }

        $remoteStream = $flySystem->readStream($remotePath);
        if (!$remoteStream) {
            return false;
        }

        $fh = fopen($localPath, 'w');
        $result = (bool) stream_copy_to_stream($remoteStream, $fh);
        fclose($fh);

        return $result;
    }

    public function has(string $remotePath): bool
    {
        $flySystem = $this->init();

        return $flySystem->has($remotePath);
    }

    public function delete(string $path): void
    {
        $flySystem = $this->init();

        try {
            $flySystem->delete($path);
        } catch (FileNotFoundException $exception) {
            return;
        }
    }
}
