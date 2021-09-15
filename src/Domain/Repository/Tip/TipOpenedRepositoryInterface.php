<?php

namespace Proximum\Vimeet\Domain\Repository\Tip;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipOpened;
use Proximum\Vimeet\Domain\Model\User;

interface TipOpenedRepositoryInterface
{
    public function add(TipOpened $tipOpened): void;

    public function set(TipOpened $tipOpened): void;

    public function isOpened(Tip $tip, User $user): bool;

    public function getByTipAndUser(Tip $tip, User $user): ?TipOpened;
}
