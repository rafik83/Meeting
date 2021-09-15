<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class ExtraData
{
    /** @var int */
    private $id;

    /** @var Sheet */
    private $sheet;

    /** @var string */
    private $name;

    /** @var string|null */
    private $value;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /**
     * @param Sheet              $sheet
     * @param string             $name
     * @param string|null        $value
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Sheet $sheet, string $name, string $value = null, \DateTimeInterface $createdAt)
    {
        $this->sheet = $sheet;
        $this->name = $name;
        $this->value = $value;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}
