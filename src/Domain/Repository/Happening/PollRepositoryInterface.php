<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;

interface PollRepositoryInterface
{
    public function add(Poll $poll): void;
    public function update(Poll $poll): void;
    public function delete(Poll $poll): void;

    /**
     * @return Poll[]
     */
    public function findByHappening(Happening $happening, ?string $status = null): array;

    public function findById(int $pollId): ?Poll;
}
