<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class MemberSpaceViewQueryHandler
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var NavigationBuilder
     */
    private $navigationBuilder;

    /**
     * MemberSpaceViewQueryHandler constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     * @param NavigationBuilder   $navigationBuilder
     */
    public function __construct(TemplateDataFactory $templateDataFactory, NavigationBuilder $navigationBuilder)
    {
        $this->templateDataFactory = $templateDataFactory;
        $this->navigationBuilder   = $navigationBuilder;
    }

    /**
     * @param MemberSpaceViewQuery $memberSpaceQuery
     *
     * @return null|CategoryView
     */
    public function handle(MemberSpaceViewQuery $memberSpaceQuery)
    {
        $participant = $memberSpaceQuery->sheet->getUserParticipant($memberSpaceQuery->user);

        if ($participant === null) {
            return null;
        }

        $templateData = $this->templateDataFactory->createProfileTemplate($participant, $memberSpaceQuery->locale);
        $linksView    = [];

        if (!empty($templateData->getProfileObjects())) {
            $linksView[] = new LinkView(
                'navigation.links.member_space.profile',
                $this->navigationBuilder->getRoute('event_account_participant', [
                    'sheet'       => $memberSpaceQuery->sheet->getId(),
                    'participant' => $participant->getId(),
                ])
            );
        }

        if (!empty($objects = $templateData->getAvatarObjects())) {
            $linksView[] = new LinkView(
                'navigation.links.member_space.avatar',
                $this->navigationBuilder->getRoute('event_account_participant_avatar', [
                    'sheet'       => $memberSpaceQuery->sheet->getId(),
                    'participant' => $participant->getId(),
                    'key'         => array_keys($objects)[0],
                ])
            );
        }

        if (!empty($templateData->getCompanyObjects())) {
            $linksView[] = new LinkView(
                'navigation.links.member_space.company',
                $this->navigationBuilder->getRoute('event_account_participant_company', [
                    'sheet'       => $memberSpaceQuery->sheet->getId(),
                    'participant' => $participant->getId(),
                ])
            );
        }

        return new CategoryView(
            Category::MEMBER_SPACE,
            Category::MEMBER_SPACE_ICON,
            $linksView
        );
    }
}
