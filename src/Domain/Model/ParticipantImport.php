<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;

class ParticipantImport
{
    /** @var int */
    private $id;

    /** @var Type */
    private $type;

    /** @var array */
    private $mapping;

    /** @var array */
    private $result;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var ImportMapping|null */
    private $importMapping;

    /**
     * @param Type               $type
     * @param array              $result
     * @param array              $mapping
     * @param \DateTimeInterface $createdAt
     * @param ImportMapping|null $importMapping
     */
    public function __construct(
        Type $type,
        array $result,
        array $mapping,
        \DateTimeInterface $createdAt,
        ?ImportMapping $importMapping = null
    ) {
        $this->type = $type;
        $this->result = $result;
        $this->mapping = $mapping;
        $this->createdAt = $createdAt;
        $this->importMapping = $importMapping;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getImportMapping(): ?ImportMapping
    {
        return $this->importMapping;
    }

    public function hasImportMapping(): bool
    {
        return null !== $this->importMapping;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function hasDiffInMapping(): bool
    {
        if (!$this->hasImportMapping()) {
            return true;
        }

        return $this->mapping !== $this->importMapping->getMapping();
    }
}
