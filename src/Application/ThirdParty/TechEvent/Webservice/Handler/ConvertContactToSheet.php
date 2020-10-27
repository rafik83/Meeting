<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration\Condition\TypeConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer\ContactNormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;
use Psr\Log\LoggerInterface;

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

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var LoggerInterface */
    private $logger;

    /** @var TypeConverter */
    private $typeConverter;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        ConvertToParticipantHandler $convertToParticipantHandler,
        \DateTimeInterface $dateTime,
        ContactNormalizer $contactNormalizer,
        UserRepositoryInterface $userRepository,
        TypeConverter $typeConverter,
        LoggerInterface $logger
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->contactNormalizer = $contactNormalizer;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->typeConverter = $typeConverter;
    }

    /**
     * @param Event          $event
     * @param Type[]         $types
     * @param TemplateData[] $registrationTemplates
     * @param TemplateData[] $sheetTemplates
     * @param array          $contact
     * @param array          $eventConfiguration
     */
    public function handle(
        Event $event,
        array $types,
        array $registrationTemplates,
        array $sheetTemplates,
        array $contact,
        array $eventConfiguration
    ): void {
        $mandatoryKeys = $eventConfiguration['mandatory_keys'] ?? [];
        $emailKey = $mandatoryKeys['email'];
        $identifierKey = $mandatoryKeys['identifier'];
        $identifierValue = $contact[$identifierKey];

        if (!isset($contact[$emailKey])) {
            $this->logger->warning(
                sprintf('VIMEET - A user has no email. The identifier key is "%s"', $identifierValue)
            );

            return;
        }

        $type = $this->typeConverter->convert($types, $eventConfiguration['types'], $contact);

        if (!$type instanceof Type) {
            $this->logger->info(
                sprintf(
                    'VIMEET - No type match the contact with identifier : %s',
                    $identifierValue
                )
            );

            return;
        }

        $loginDataKey = $mandatoryKeys['loginData'] ?? null;
        // login data should not be normalized (no trim, etc..)
        $loginData = $contact[$loginDataKey] ?? null;

        // An identifier may be sent with an md5 hash.
        // This identifier has to be updated and stored separately
        $identifierMD5Key = $mandatoryKeys['identifierMD5'] ?? null;
        $valueMD5 = $contact[$identifierMD5Key] ?? null;

        $countryKey = $mandatoryKeys['country'] ?? null;
        $email = mb_strtolower($contact[$emailKey]);

        $contact = $this->contactNormalizer->normalize(
            $contact,
            $eventConfiguration['normalize'] ?? [],
            $countryKey
        );

        $registrationTemplate = $this->getTemplateForType($type, $registrationTemplates);
        $sheetTemplate = $this->getTemplateForType($type, $sheetTemplates);

        $registrationTemplate->clear();
        $sheetTemplate->clear();

        // Mapping is indexed by type (see src/Application/ThirdParty/TechEvent/Webservice/Configuration/Mapping.md)
        $mapping = $eventConfiguration['types'][$type->getId()]['mapping'] ?? [];

        $dataIndexedByTag = $this->getDataIndexedByTag($contact, $mapping);

        $participant = $this->convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event,
                $type,
                $email,
                $event->getLocaleFallback(),
                $dataIndexedByTag,
                $registrationTemplate,
                $sheetTemplate,
                ExtraDataType::IMPORTED_FROM_TECH_EVENT
            )
        );

        $user = null;

        if ($participant instanceof Participant) {
            $user = $participant->getUser();

            $this->userEventExtraDataRepository->add(
                new User\Event\ExtraData(
                    $user,
                    $event,
                    ExtraDataType::IMPORTED_FROM_TECH_EVENT,
                    $contact[$identifierKey],
                    $this->dateTime
                )
            );
        }

        if (null === $user) {
            $user = $this->userRepository->findByEmail($email);
        }

        if ($user instanceof User) {
            if (null !== $loginData) {
                $this->userEventExtraDataRepository->removeForUserAndEventAndName(
                    $user,
                    $event,
                    ExtraDataType::TECH_EVENT_LOGIN_DATA
                );

                $this->userEventExtraDataRepository->add(
                    new User\Event\ExtraData(
                        $user,
                        $event,
                        ExtraDataType::TECH_EVENT_LOGIN_DATA,
                        $loginData,
                        $this->dateTime
                    )
                );
            }

            if (null !== $valueMD5) {
                $this->userEventExtraDataRepository->add(
                    new User\Event\ExtraData(
                        $user,
                        $event,
                        ExtraDataType::TECH_EVENT_IDENTIFIER_MD5,
                        $valueMD5,
                        $this->dateTime
                    )
                );
            }
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

    private function getTemplateForType(Type $type, array $templates): TemplateData
    {
        return $templates[$type->getId()];
    }
}
