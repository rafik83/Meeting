<?php

namespace Proximum\Vimeet\Application\Command\Participant\Upload;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadableObjectInterface;

class UploadFile implements Command
{
    /** @var Sheet */
    private $sheet;

    /** @var User */
    private $user;

    /** @var UploadableObjectInterface */
    private $object;

    /** @var array */
    private $data;

    /** @var bool */
    private $isSheetData;

    public function __construct(
        Sheet $sheet,
        User $user,
        UploadableObjectInterface $object,
        array $data,
        bool $isSheetData
    ) {
        $this->sheet = $sheet;
        $this->object = $object;
        $this->data = $data;
        $this->user = $user;
        $this->isSheetData = $isSheetData;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getEvent(): Event
    {
        return $this->sheet->getEvent();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getObject(): UploadableObjectInterface
    {
        return $this->object;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isSheetData(): bool
    {
        return $this->isSheetData;
    }
}
