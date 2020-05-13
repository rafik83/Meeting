<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\StaticFormulation;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQuery;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQueryHandler;
use Proximum\Vimeet\Application\View\StaticFormulation\Generic\GenericStaticFormulationView;
use Proximum\Vimeet\Application\View\StaticFormulation\StaticFormulationListView;
use Proximum\Vimeet\Application\View\StaticFormulation\StaticFormulationView;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class StaticFormulationListViewQueryHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var CustomizedStaticFormulationViewQueryHandler */
    private $customizedStaticFormulationViewQueryHandler;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        StaticFormulationRepositoryInterface $staticFormulationRepository,
        TranslatorInterface $translator,
        CustomizedStaticFormulationViewQueryHandler $customizedStaticFormulationViewQueryHandler
    ) {
        $this->typeRepository = $typeRepository;
        $this->staticFormulationRepository = $staticFormulationRepository;
        $this->translator = $translator;
        $this->customizedStaticFormulationViewQueryHandler = $customizedStaticFormulationViewQueryHandler;
    }

    public function handle(StaticFormulationListViewQuery $query): StaticFormulationListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);
        $types = $this->typeRepository->getTypesByEvent($query->event);
        $staticFormulationsCustomized = $this->staticFormulationRepository->findByEvent($query->event);

        $customized = [];

        foreach ($staticFormulationsCustomized as $staticFormulationCustomized) {
            $key = $staticFormulationCustomized->getKey();
            $customizedStaticFormulationView = $this->customizedStaticFormulationViewQueryHandler->handle(
                new CustomizedStaticFormulationViewQuery($staticFormulationCustomized, $locale)
            );

            $customized[$key]['staticFormulation'][] = $customizedStaticFormulationView;

            foreach ($customizedStaticFormulationView->typeTitles as $typeId => $typeTitle) {
                $customized[$key]['typesUsed'][$typeId] = $typeTitle;
            }
        }

        $staticFormulations = [];
        $generics = [];
        foreach (Constant::STATIC_FORMULATION_LIST as $key => $data) {
            $typesUsed = $customized[$key]['typesUsed'] ?? [];
            $remainingTypes = array_filter($types, static function (Type $type) use ($typesUsed) {
                return !isset($typesUsed[$type->getId()]);
            });

            $generics[$key] = new GenericStaticFormulationView(
                $key,
                $this->translator->trans($data['label'], [], 'messages', $locale),
                array_map(static function (Type $type) use ($locale) {
                    return $type->getTitle($locale);
                }, $remainingTypes)
            );

            $staticFormulations[] = new StaticFormulationView(
                $key,
                $generics[$key],
                $customized[$key]['staticFormulation'] ?? []
            );
        }

        return new StaticFormulationListView($staticFormulations);
    }
}
