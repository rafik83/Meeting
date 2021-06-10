<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class Poll
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_HIDDEN = 'hidden';

    private int $id;
    private Happening $happening;
    private User $createdBy;
    private DateTimeInterface $createdAt;
    private string $status;
    private string $title;
    private bool $multipleChoice;
    private Collection $pollChoices;

    public function __construct(
        Happening $happening,
        User $createdBy,
        DateTimeInterface $createdAt,
        string $title,
        array $choices,
        bool $multipleChoice
    ) {
        $this->happening = $happening;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->status = self::STATUS_DRAFT;
        $this->title = $title;
        $this->multipleChoice = $multipleChoice;
        $this->pollChoices = $this->createChoicesFromArray($choices);
    }

    public function update(string $title, array $pollChoices, bool $multipleChoice): void
    {
        $this->title = $title;
        $this->pollChoices->clear();
        $this->pollChoices = $this->createChoicesFromArray($pollChoices);
        $this->multipleChoice = $multipleChoice;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setHidden(): void
    {
        $this->status = self::STATUS_HIDDEN;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function setPublished(): void
    {
        $this->status = self::STATUS_PUBLISHED;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isMultipleChoice(): bool
    {
        return $this->multipleChoice;
    }

    public function getPollChoices(): Collection
    {
        return $this->pollChoices;
    }

    /**
     * @return PollChoice[]
     */
    public function getPollChoicesArray(): array
    {
        return $this->pollChoices->toArray();
    }

    /**
     * @param string[] $choices
     */
    private function createChoicesFromArray(array $choices): Collection
    {
        return new ArrayCollection(array_map(
            fn (array $choice) => new PollChoice($this, $choice['content']),
            $choices
        ));
    }
}
