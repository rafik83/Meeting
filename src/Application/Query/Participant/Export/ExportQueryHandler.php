<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Participant\Export;

use Proximum\Vimeet\Application\View\Participant\Export\ParticipantListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;

class ExportQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        TemplateDataFactory $templateDataFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->templateDataFactory = $templateDataFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ExportQuery $exportQuery
     *
     * @return ParticipantListView
     */
    public function handle(ExportQuery $exportQuery): ParticipantListView
    {
        $participants = $this->participantRepository->findByIds($exportQuery->participantIds);

        $registrationColumns = [];
        $typesHandled = [];
        $participantViews= [];

        $productColumns = $this->prepareProductColumns($exportQuery->event);

        foreach ($participants as $participant) {
            if (!isset($typesHandled[$participant->getSheet()->getType()->getId()])) {
                $this->prepareRegistrationColumns(
                    $registrationColumns,
                    $participant->getSheet()->getType(),
                    $exportQuery->locale,
                    $exportQuery->event->getFallback()
                );

                $typesHandled[$participant->getSheet()->getType()->getId()] = true;
            }

            $participantViews[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery($exportQuery->event, $participant, $exportQuery->locale)
            );
        }

        return new ParticipantListView(
            $exportQuery->locale,
            $participantViews,
            $registrationColumns,
            $productColumns
        );
    }

    private function prepareRegistrationColumns(
        array &$registrationColumns,
        Type $type,
        string $locale,
        string $fallback
    ): void {
        $template = $this->templateDataFactory->createRegistrationFromType($type, $locale);

        foreach ($template->getProfileObjects() as $registrationObject) {
            if ($registrationObject instanceof ExportableObjectInterface) {
                $key = $registrationObject->getKey();

                if (!isset($registrationColumns[$key])) {
                    $registrationColumns[$key] =  $registrationObject->getExportableFieldname(
                        $locale,
                        $fallback
                    );
                }
            }
        }
    }

    private function prepareProductColumns(Event $event): array
    {
        $columns = [];

        $products = $this->productRepository->findParticipantAndAttributableByEvent($event);

        foreach ($products as $product) {
            $key = $product->isParticipant()
                ? sprintf('participant_%s', $product->getId())
                : sprintf('option_%s', $product->getId())
            ;

            $columns[$key] = $product->getName();
        }

        return $columns;
    }
}
