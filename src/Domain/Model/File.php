<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class File
{
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_UPLOADED_OBJECTS_ZIP = 'uploaded_objects_zip';
    public const TYPE_EXPORT_FORM_TEMPLATE_DATA = 'export_form_template_data';
    public const TYPE_EXPORT_ROOMING_LIST = 'export_rooming_list';
    public const TYPE_EXPORT_PRODUCT_LIST = 'export_product_list';
    public const TYPE_EXPORT_SHEET_LIST = 'export_sheet_list';
    public const TYPE_PRINT_INVOICES = 'print_invoices';

    /** @var int */
    private $id;

    /** @var string */
    private $path;

    /** @var string */
    private $type;

    /** @var DateTimeInterface */
    private $createdAt;

    public function __construct(string $path, DateTimeInterface $createdAt, string $type = self::TYPE_UNKNOWN)
    {
        $this->path = $path;
        $this->createdAt = $createdAt;
        $this->type = $type;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return hash('sha256', $this->getPath() . $this->getCreatedAt()->format('YmdHis') . $this->getId());
    }
}
