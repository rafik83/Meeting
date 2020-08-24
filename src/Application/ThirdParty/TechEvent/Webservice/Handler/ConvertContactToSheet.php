<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Data\Type as DataType;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer\ContactNormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class ConvertContactToSheet
{
    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var ContactNormalizer */
    private $contactNormalizer;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        ConvertToParticipantHandler $convertToParticipantHandler,
        \DateTimeInterface $dateTime,
        ContactNormalizer $contactNormalizer
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->contactNormalizer = $contactNormalizer;
    }

    public function handle(
        Event $event,
        Type $type,
        TemplateData $registrationTemplate,
        TemplateData $sheetTemplate,
        array $contact,
        array $eventConfiguration
    ): void {
        $registrationTemplate->clear();
        $sheetTemplate->clear();

        $contact = $this->contactNormalizer->normalize($contact, $eventConfiguration['normalize'] ?? []);
        $dataIndexedByTag = $this->getDataIndexedByTag($contact, $eventConfiguration['mapping'] ?? []);

        $participant = $this->convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event,
                $type,
                $contact[DataType::EMAIL],
                $event->getLocaleFallback(),
                $dataIndexedByTag,
                $registrationTemplate,
                $sheetTemplate,
                ExtraDataType::IMPORTED_FROM_TECH_EVENT
            )
        );

        if ($participant instanceof Participant) {
            $this->userEventExtraDataRepository->add(
                new User\Event\ExtraData(
                    $participant->getUser(),
                    $event,
                    ExtraDataType::IMPORTED_FROM_TECH_EVENT,
                    $contact[DataType::ID_CONTACT],
                    $this->dateTime
                )
            );
        }
    }

    private function getDataIndexedByTag(array $contact, array $mapping): array
    {
        $dataIndexedByTag = [];

        foreach ($mapping as $key => $tag) {
            if (isset($contact[$key])) {
                $dataIndexedByTag[$tag] = $contact[$key];
            }
        }

        return $dataIndexedByTag;
    }
}
