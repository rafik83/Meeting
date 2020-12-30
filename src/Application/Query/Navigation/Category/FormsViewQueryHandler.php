<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class FormsViewQueryHandler
{
    /** @var FormTemplateRepositoryInterface */
    private $formTemplateRepository;

    /** @var RouterInterface */
    private $router;

    public function __construct(FormTemplateRepositoryInterface $formTemplateRepository, RouterInterface $router)
    {
        $this->formTemplateRepository = $formTemplateRepository;
        $this->router = $router;
    }

    public function handle(FormsViewQuery $formsViewQuery): ?CategoryView
    {
        $sheet = $formsViewQuery->sheet;
        $type = $sheet->getType();
        $formTemplateViews = $this->formTemplateRepository->getPublishedFormTemplateViewByType(
            $type,
            $formsViewQuery->locale
        );

        if (empty($formTemplateViews)) {
            return null;
        }

        $participant = $this->getParticipant($sheet, $formsViewQuery->user);
        $linksView = [];

        foreach ($formTemplateViews as $formTemplateView) {
            $linksView[] = new LinkView(
                $formTemplateView->title,
                $this->router->generate('event_participant_fill_form', [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'formTemplate' => $formTemplateView->formTemplateId,
                    'step' => 1,
                ])
            );
        }

        return new CategoryView(
            $this->getTitle($formsViewQuery->staticFormulation, $formsViewQuery->locale),
            Category::FORMS_ICON,
            $linksView,
            true,
            true
        );
    }

    private function getTitle(?StaticFormulation $staticFormulation, string $locale): string
    {
        if (null !== $staticFormulation) {
            return $staticFormulation->getTitle($locale);
        }

        return Category::FORMS;
    }

    private function getParticipant(Sheet $sheet, User $user): Participant
    {
        $participant = $sheet->getUserParticipant($user);

        if ($participant instanceof Participant) {
            return $participant;
        }

        return $sheet->getFirstParticipant();
    }
}
