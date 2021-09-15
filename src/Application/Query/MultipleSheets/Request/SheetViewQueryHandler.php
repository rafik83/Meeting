<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;

class SheetViewQueryHandler
{
    /**
     * @var ValidationRequiredChecker
     */
    private $validationRequiredChecker;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * SheetViewQueryHandler constructor.
     *
     * @param ValidationRequiredChecker $validationRequiredChecker
     * @param RouterInterface           $router
     */
    public function __construct(
        ValidationRequiredChecker $validationRequiredChecker,
        RouterInterface $router
    ) {
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->router                    = $router;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query)
    {
        $validationRequired = $this->validationRequiredChecker->handle(
            $query->sheet,
            $query->user
        );

        $participant = $query->sheet->getUserParticipant($query->user);

        if (null !== $participant) {
            $redirectTo = $query->sheet->hasGroup()
                ? $this->router->generate(
                    'event_sheet_group_requests_list',
                    [
                        'sheetGroup' => $query->sheet->getGroup()->getId(),
                    ]
                )
                : $this->router->generate(
                    'event_meeting_request_merged_list',
                    [
                        'sheet' => $query->sheet->getId(),
                    ]
                );

            $validatePhoneLink = $this->router->generate('event_user_phone_redirect_to_validation', [
                'sheet'       => $query->sheet->getId(),
                'participant' => $participant->getId(),
                'redirectTo'  => $redirectTo,
            ]);
        }

        return new SheetView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet,
            $query->sheet->getType()->getTitle($query->locale),
            $validationRequired,
            $validatePhoneLink ?? null
        );
    }
}
