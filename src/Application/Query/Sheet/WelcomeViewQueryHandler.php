<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\View\Sheet\WelcomeView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;

class WelcomeViewQueryHandler
{
    /** @var HappeningsAccessChecker */
    private $happeningsAccessChecker;

    /** @var SessionInterface */
    private $session;

    /**
     * @param HappeningsAccessChecker $happeningsAccessChecker
     * @param SessionInterface        $session
     */
    public function __construct(HappeningsAccessChecker $happeningsAccessChecker, SessionInterface $session)
    {
        $this->happeningsAccessChecker = $happeningsAccessChecker;
        $this->session = $session;
    }

    /**
     * @param WelcomeViewQuery $welcomeViewQuery
     *
     * @return null|WelcomeView
     */
    public function handle(WelcomeViewQuery $welcomeViewQuery): ?WelcomeView
    {
        if (!\in_array(true, $this->session->getFromFlashBag('first_registration'), true)) {
            return null;
        }

        if (!$welcomeViewQuery->sheet->getEvent()->isWelcomeEnabled()) {
            return null;
        }

        $hasPackage = null !== $welcomeViewQuery->sheet->getPackage()
            && $welcomeViewQuery->sheet->getPackage()->isPassable();

        $hasProgram = $this->happeningsAccessChecker->allowedToAccess($welcomeViewQuery->sheet->getEvent());

        return new WelcomeView($hasPackage, $hasProgram);
    }
}
