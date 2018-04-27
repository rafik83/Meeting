<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Upload;

use Proximum\Vimeet\Domain\Template\TemplateObject\UploadableObjectInterface;

class UploadFile
{
    /** @var UploadableObjectInterface */
    private $object;

    /** @var array */
    private $data;

    public function __construct(UploadableObjectInterface $object, array $data)
    {
        $this->object = $object;
        $this->data = $data;
    }

    public function getObject(): UploadableObjectInterface
    {
        return $this->object;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
