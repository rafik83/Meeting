<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\View\Participant\Export\ParticipantView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class ParticipantViewQueryHandler
{
    /** @var null|\IntlDateFormatter */
    private $timeFormatter;

    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var HasRemainingToPay */
    private $hasRemainingToPay;

    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        TemplateDataFactory $templateDataFactory,
        HasRemainingToPay $hasRemainingToPay,
        TranslatorInterface $translator,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->hasRemainingToPay = $hasRemainingToPay;
        $this->translator = $translator;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
    }

    public function handle(ParticipantViewQuery $query): ParticipantView
    {
        if (null === $this->timeFormatter) {
            $this->timeFormatter = \IntlDateFormatter::create(
                $query->locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE,
                $query->event->getTimeZone()
            );
        }

        $registrationData = $this->getRegistrationData($query->participant, $query->locale);

        $participantProductId = null !== $query->participant->getParticipantProduct()
            ? $query->participant->getParticipantProduct()->getId()
            : null
        ;

        $attributableProducts = $this->prepareAttributableProducts($query->participant);

        $view = new ParticipantView(
            $query->participant->getSheet()->getId(),
            $query->participant->getSheet()->getType()->getTitle($query->locale),
            $query->participant->getSheet()->getTitle(),
            $query->participant->getSheet()->isEnabled(),
            $query->participant->getUser()->getId(),
            $query->participant->getId(),
            $query->participant->getEmail(),
            $this->timeFormatter->format($query->participant->getSheet()->getCreatedAt()),
            $this->happeningParticipationRepository->hasParticipationForUserAndEvent(
                $query->participant->getUser(),
                $query->event
            ),
            !$this->hasRemainingToPay->isSatisfiedBy($query->participant->getSheet()),
            $participantProductId,
            $attributableProducts,
            $registrationData
        );

        return $view;
    }

    private function prepareAttributableProducts(Participant $participant): array
    {
        $attributableProducts = $this->productAttributedToParticipantRepository->findByParticipant($participant);

        $attributableProductsIds = [];

        foreach ($attributableProducts as $attributableProduct) {
            $productId = $attributableProduct->getProduct()->getId();
            $attributableProductsIds[sprintf('option_%s', $productId)] = $productId;
        }

        return $attributableProductsIds;
    }

    /**
     * @param Participant $participant
     * @param string      $locale
     *
     * @return array
     */
    private function getRegistrationData(Participant $participant, string $locale): array
    {
        $registrationData = [];
        $template = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $fieldContent = $registrationObject->getExportableContent([], $locale);

                if ($registrationObject instanceof Gender && !empty($fieldContent)) {
                    $fieldContent = $this->translator->trans(sprintf('gender.%s', $fieldContent), [], null, $locale);
                }

                if ($registrationObject instanceof BooleanObject) {
                    $fieldContent = $this->translator->trans(
                        sprintf('boolean.%s', $fieldContent ? 'yes' : 'no'),
                        [],
                        null,
                        $locale
                    );
                }

                $registrationData[$registrationObject->getKey()] = $fieldContent;
            }
        }

        return $registrationData;
    }
}
