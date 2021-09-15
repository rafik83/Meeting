<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Form;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Template\Form\Create;
use Proximum\Vimeet\Application\Command\Template\Form\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;

class CreateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);
        $event->getFallback()->shouldBeCalled()->willReturn('fr');

        $create = new Create($event->reveal());
        $create->title = 'BO Title';
        $create->translations = [
            'fr' => [
                'title' => 'Titre en français',
            ],
            'en' => [
                'title' => 'Title in English',
            ]
        ];

        $dateTime = new \DateTime();
        $template = new FormTemplate(
            $event->reveal(),
            'BO Title',
            [],
            ['fr', 'en'],
            'fr',
            $dateTime
        );
        $template->translateTitles([
            'fr' => [
                'title' => 'Titre en français',
            ],
            'en' => [
                'title' => 'Title in English',
            ]
        ]);

        $formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $formTemplateRepository->add($template)->shouldBeCalled();
        $handler = new CreateHandler($formTemplateRepository->reveal(), $dateTime);
        $handler->handle($create);
    }
}
