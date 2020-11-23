<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use DateTimeInterface;
use Proximum\Vimeet\Application\Command\Happening\AbstractHappeningCommand;
use Proximum\Vimeet\Application\View\Happening\ApiSpeakerView;
use Proximum\Vimeet\Application\View\Happening\ApiView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

/**
 * Class GetApiListQueryHandler
 * Provide PUBLIC info on happening objects
 * This handler must not expose private info
 */
class GetApiListQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->happeningRepository = $happeningRepository;
    }

    public function handle(GetApiListQuery $query)
    {
        $happenings = $this->happeningRepository->findByEventAndTypes($query->event, $query->participantTypeIds);


        return array_map(function (Happening $happening) use ($query) {
            $speakers = array_map(function (Speaker $speaker) use ($query) {
                    return new ApiSpeakerView(
                        $speaker->getFirstname(),
                        $speaker->getLastname(),
                        $speaker->getPosition($query->locale),
                        // todo: use imagine to provide resized versions of pictures
                        $query->baseUrl.$speaker->getPhoto(),
                        $query->baseUrl.$speaker->getLogo()
                    );
                },
                $happening->getSpeakers()
            );

            $happeningType = AbstractHappeningCommand::TYPE_DEFAULT;
            if ($happening->isInteractiveWebinar()) {
                $happeningType = AbstractHappeningCommand::TYPE_WEBINAR_INTERACTIVE;
            } else if ($happening->isVideoWebinar()) {
                $happeningType = AbstractHappeningCommand::TYPE_WEBINAR_VIDEO;
            } else if ($happening->isVideoWebinar()) {
                $happeningType = AbstractHappeningCommand::TYPE_WEBINAR;
            }

            return new ApiView(
                $happening->getId(),
                $happening->getTitle($query->locale),
                $happening->getDescription($query->locale),
                $happening->getBegin()->format(DateTimeInterface::ISO8601),
                $happening->getEnd()->format(DateTimeInterface::ISO8601),
                $happeningType,
                $happening->getCategory()->getTitle($query->locale),
                $speakers,
                array_map(function (Type $type) {
                    return $type->getId();
                }, $happening->getTypes())
            );
        }, $happenings);
    }
}
