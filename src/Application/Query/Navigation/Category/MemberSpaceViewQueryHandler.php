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

class MemberSpaceViewQueryHandler
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * MemberSpaceViewQueryHandler constructor.
     *
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
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

        if(!empty($templateData->getProfileObjects())) {
            $linksView[] = new LinkView('navigation.links.member_space.profile', '');
        }

        if (!empty($templateData->getAvatarObjects())) {
            $linksView[] = new LinkView('navigation.links.member_space.avatar', '');
        }

        if (!empty($templateData->getCompanyObjects())) {
            $linksView[] = new LinkView('navigation.links.member_space.company', '');
        }

        return new CategoryView(
            Category::MEMBER_SPACE,
            Category::MEMBER_SPACE_ICON,
            $linksView
        );
    }
}
