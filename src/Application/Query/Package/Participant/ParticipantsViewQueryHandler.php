<?php

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\View\Package\ParticipantsView;

class ParticipantsViewQueryHandler
{
    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var ParticipantProductViewQueryHandler */
    private $participantProductViewQueryHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /**
     * @param ParticipantViewQueryHandler        $participantViewQueryHandler
     * @param ParticipantProductViewQueryHandler $participantProductViewQueryHandler
     * @param SerializerAdapterInterface         $serializerAdapter
     */
    public function __construct(
        ParticipantViewQueryHandler $participantViewQueryHandler,
        ParticipantProductViewQueryHandler $participantProductViewQueryHandler,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->participantProductViewQueryHandler = $participantProductViewQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @param ParticipantsViewQuery $participantsViewQuery
     *
     * @return ParticipantsView
     */
    public function handle(ParticipantsViewQuery $participantsViewQuery)
    {
        $locale = $participantsViewQuery->locale;
        $sheet = $participantsViewQuery->sheet;

        $participantView = [];

        foreach ($sheet->getParticipantsArray() as $participant) {
            $participantView[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $participant,
                    $locale
                )
            );
        }

        $participantProductViews = $this->participantProductViewQueryHandler->handle(
            new ParticipantProductViewQuery($sheet, $locale)
        );

        $participantsView = new ParticipantsView(
            $participantView,
            $participantProductViews,
            $this->serializerAdapter->serialize($participantProductViews, 'json', ['locale' => $locale])
        );

        return $participantsView;
    }
}
