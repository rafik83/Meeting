<?php


namespace Proximum\Vimeet\Tests\Application\Command\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParameters;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParametersHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class UpdateParametersHandlerTest extends TestCase
{
    public function testHandle()
    {
        $createdAt = new \DateTime('2018-11-20 12:05:00');

        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $formTemplate = new FormTemplate($event->reveal(), 'Form title', [], ['fr', 'en'], 'fr', $createdAt);
        $formTemplate->translateTitles(['fr' => ['title' => 'Logistique'], 'en' => ['title' => 'Logistic']]);

        $expectedFormTemplate = Argument::that(
            function (FormTemplate $formTemplate) {
                return 'My form title' === $formTemplate->getTitle()
                    && true === $formTemplate->isPublished()
                    && 'Mes infos logistique' === $formTemplate->getLocalizedTitle('fr')
                    && 'My logistic information' === $formTemplate->getLocalizedTitle('en');
            }
        );

        $formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $formTemplateRepository->update($expectedFormTemplate)->shouldBeCalled();

        $updateParameters = new UpdateParameters($formTemplate);
        $updateParameters->title = 'My form title';
        $updateParameters->published = true;
        $updateParameters->translations = [
            'fr' => ['title' => 'Mes infos logistique'],
            'en' => ['title' => 'My logistic information'],
        ];

        $updateParametersHandler = new UpdateParametersHandler($formTemplateRepository->reveal());
        $updateParametersHandler->handle($updateParameters);
    }
}
