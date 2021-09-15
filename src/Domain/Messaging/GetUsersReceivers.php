<?php

namespace Proximum\Vimeet\Domain\Messaging;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareUserCampaignMailView;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;

class GetUsersReceivers
{
    /** @var SubstitutionHandler */
    private $substitutionHandler;

    /** @var SheetGuesser */
    private $sheetGuesser;

    public function __construct(SubstitutionHandler $substitutionHandler, SheetGuesser $sheetGuesser)
    {
        $this->substitutionHandler = $substitutionHandler;
        $this->sheetGuesser = $sheetGuesser;
    }

    public function __invoke(array $users, Message $message): array
    {
        $event = $message->getEvent();
        $receivers = [];

        /** @var User $user */
        foreach ($users as $user) {
            $locale = $event->getAvailableLocale($user->getLocale());

            try {
                $sheet = $this->sheetGuesser->getUserSheet($user, $event, $locale);
            } catch (\Exception $exception) {
                continue;
            }

            $substitutionResult = $this->substitutionHandler->handle(
                new PrepareUserCampaignMailView(
                    $event,
                    $user,
                    $locale,
                    $sheet
                ),
                $message
            );

            $receiverView = new ReceiverView(
                $user->getEmail(),
                $substitutionResult->getAllSubstitutions(),
                $locale
            );

            $receivers[$user->getEmail()] = $receiverView;
        }

        return $receivers;
    }
}
