<?php

namespace Proximum\Vimeet\Application\View\Home;

use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;

class HomeDispatchAnonymousView
{
    /** @var string */
    public $type;

    /**
     * HomeDispatchAnonymousView constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (false === $this->isGivenTypeValid($type)) {
            throw new \InvalidArgumentException('Given type is invalid');
        }

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isRegistrationNotOpen(): bool
    {
        return HomeDispatchAnonymousUser::TYPE_REGISTRATION_NOT_OPEN === $this->type;
    }

    /**
     * @return bool
     */
    public function isRegistrationClosed(): bool
    {
        return HomeDispatchAnonymousUser::TYPE_REGISTRATION_CLOSED === $this->type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    private function isGivenTypeValid(string $type): bool
    {
        return in_array($type, [
            HomeDispatchAnonymousUser::TYPE_REGISTRATION_CLOSED,
            HomeDispatchAnonymousUser::TYPE_REGISTRATION_NOT_OPEN,
        ]);
    }
}
