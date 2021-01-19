<?php

namespace Proximum\Vimeet\Application\Security;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQueryHandler;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ValidateMobileProcessAccessChecker
{
    /**
     * @var TipTranslationViewByUserQueryHandler
     */
    private $tipTranslationViewByUserQueryHandler;

    /**
     * ValidateMobileProcessAccessChecker constructor.
     *
     * @param TipTranslationViewByUserQueryHandler $tipTranslationViewByUserQueryHandler
     */
    public function __construct(TipTranslationViewByUserQueryHandler $tipTranslationViewByUserQueryHandler)
    {
        $this->tipTranslationViewByUserQueryHandler = $tipTranslationViewByUserQueryHandler;
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     *
     * @return bool
     */
    public function allowToAccess(Event $event, User $user, string $locale): bool
    {
        $tipTranslationViews = $this->tipTranslationViewByUserQueryHandler->handle(
            new TipTranslationViewByUserQuery(
                $event,
                $user,
                TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                $locale
            )
        );

        return !empty($tipTranslationViews);
    }
}
