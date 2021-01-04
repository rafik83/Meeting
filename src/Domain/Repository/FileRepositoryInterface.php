<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\File;

interface FileRepositoryInterface
{
    /**
     * @param File $file
     */
    public function add(File $file);

    /**
     * @param int $id
     *
     * @return null|File
     */
    public function getById($id);

    /**
     * @param File $file
     */
    public function remove(File $file);

    public function findExpiredFiles(\DateTimeInterface $dateTime): array;
}
