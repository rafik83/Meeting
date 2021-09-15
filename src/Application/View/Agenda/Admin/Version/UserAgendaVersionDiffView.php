<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Version;

class UserAgendaVersionDiffView
{
    const ANSWER_NO_PHONE = 'no-phone';
    const ANSWER_NO_DIFF = 'no-diff';
    const ANSWER_DIFF = 'diff';

    /** @var string */
    public $state;

    /** @var null|string */
    public $diff;

    /**
     * @param string      $state
     * @param string|null $diff
     */
    public function __construct(string $state, string $diff = null)
    {
        $this->state = $state;
        $this->diff = $diff;
    }

    /**
     * @return bool
     */
    public function hasNoPhone(): bool
    {
        return self::ANSWER_NO_PHONE === $this->state;
    }
}
