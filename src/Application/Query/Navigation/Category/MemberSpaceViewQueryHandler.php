<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class MemberSpaceViewQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /**
     * @param TemplateDataFactory        $templateDataFactory
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(TemplateDataFactory $templateDataFactory, NavigationBuilderInterface $navigationBuilder)
    {
        $this->templateDataFactory = $templateDataFactory;
        $this->navigationBuilder   = $navigationBuilder;
    }

    /**
     * @param MemberSpaceViewQuery $memberSpaceQuery
     *
     * @return null|CategoryView
     */
    public function handle(MemberSpaceViewQuery $memberSpaceQuery): ?CategoryView
    {
        $participant = $memberSpaceQuery->sheet->getUserParticipant($memberSpaceQuery->user);

        if (null === $participant) {
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
                    'key'         => array_keys($objects)[0], // get avatar key
                ])
            );
        }

        if (!empty($templateData->getEditableSheetDataExceptedImageObjects())) {
            $linksView[] = new LinkView(
                'navigation.links.member_space.company',
                $this->navigationBuilder->getRoute('event_account_participant_company', [
                    'sheet'       => $memberSpaceQuery->sheet->getId(),
                    'participant' => $participant->getId(),
                ])
            );
        }

        $linksView[] = new LinkView(
            'navigation.links.member_space.terms_of_sale',
            $this->navigationBuilder->getRoute(
                'event_content_terms_of_sale',
                [
                    'sheet' => $memberSpaceQuery->sheet->getId(),
                ]
            )
        );

        $linksView[] = new LinkView(
            'navigation.links.member_space.privacy_policy',
            $this->navigationBuilder->getRoute(
                'event_content_terms_of_sale',
                [
                    'sheet' => $memberSpaceQuery->sheet->getId(),
                ]
            )
        );

        $categoryTitle = Category::MEMBER_SPACE;

        if (null !== $memberSpaceQuery->staticFormulation) {
            $categoryTitle = $memberSpaceQuery->staticFormulation->getTitle($memberSpaceQuery->locale);
        }

        return new CategoryView(
            $categoryTitle, Category::MEMBER_SPACE_ICON, $linksView, true, true
        );
    }
}
