<?php

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class TransactionListViewQueryHandler
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;

    /**
     * @var LocalFileStorageAdapter
     */
    private $fileStorage;

    /**
     * @var string
     */
    private $exportTransactionDirectory;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * TransactionListViewQueryHandler constructor.
     *
     * @param SerializerAdapter       $serializer
     * @param LocalFileStorageAdapter $fileStorageAdapter
     * @param string                  $exportTransactionDir
     * @param \DateTimeInterface      $dateTime
     */
    public function __construct(
        SerializerAdapter $serializer,
        LocalFileStorageAdapter $fileStorageAdapter,
        $exportTransactionDir,
        \DateTimeInterface $dateTime
    ) {
        $this->serializer                 = $serializer;
        $this->fileStorage                = $fileStorageAdapter;
        $this->exportTransactionDirectory = $exportTransactionDir;
        $this->dateTime                   = $dateTime;
    }

    /**
     * @param TransactionListViewQuery $query
     *
     * @return string
     */
    public function handle(TransactionListViewQuery $query)
    {
        $data = $this->serializer->serialize($query, 'csv',
            [
                'charset' => Charset::WINDOWS_1252,
                'csv_delimiter' => ';',
            ]
        );

        return $this->createFile($data);
    }

    /**
     * @param $data
     *
     * @return string
     */
    private function createFile(&$data)
    {
        $filePath = $this->fileStorage->create(
            $data,
            sprintf('transaction_%s.csv', $this->dateTime->getTimestamp()),
            $this->exportTransactionDirectory
        );

        return $filePath;
    }
}
