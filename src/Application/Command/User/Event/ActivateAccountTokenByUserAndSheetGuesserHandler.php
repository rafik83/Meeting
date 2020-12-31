<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;

class ActivateAccountTokenByUserAndSheetGuesserHandler
{
    /** @var ActivateAccountTokenGenerator */
    private $accountTokenGenerator;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(
        ActivateAccountTokenGenerator $accountTokenGenerator,
        SheetGuesser $sheetGuesser
    ) {
        $this->accountTokenGenerator = $accountTokenGenerator;
        $this->sheetGuesser = $sheetGuesser;
    }

    public function handle(ActivateAccountTokenByUserAndSheetGuesser $activateAccountTokenByUserAndSheetGuesser): ActivateAccountToken
    {
        /** @var User $user */
        $user = $activateAccountTokenByUserAndSheetGuesser->user;
        $locale = $activateAccountTokenByUserAndSheetGuesser->event->getAvailableLocale($user->getLocale());

        $sheet = $this->sheetGuesser
            ->getUserSheet($user, $activateAccountTokenByUserAndSheetGuesser->event, $locale);

        return $this->accountTokenGenerator->generate($user, $sheet);
    }
}
