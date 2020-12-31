<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TransliteratorAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQuery;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Domain\Repository\Sheet as SheetRepository;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class GetUploadedObjectsTreeQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $transliteratorAdapter = $this->prophesize(TransliteratorAdapterInterface::class);
        $admin = $this->prophesize(Admin::class);
        $admin->getLocale()->shouldBeCalled()->willReturn('fr');
        $type = $this->prophesize(Type::class);

        $event = $this->prophesize(Event::class);

        $user = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);

        $object1 = $this->prophesize(UploadObject::class);
        $object1->isCrypted()->shouldBeCalled()->willReturn(true);
        $object1->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $object1->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(false);
        $object1->getKey()->shouldBeCalled()->willReturn('Mb7d3M765e');
        $object1->getLabel('fr')->shouldBeCalled()->willReturn('Label 1');
        $object2 = $this->prophesize(UploadObject::class);
        $object2->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(false);
        $object2->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(true);
        $object2->isCrypted()->shouldBeCalled()->willReturn(false);
        $object2->getKey()->shouldBeCalled()->willReturn('Med79Mea70');
        $object2->getLabel('fr')->shouldBeCalled()->willReturn('Label 2');

        $templateData = $this->prophesize(TemplateData::class);
        $templateData->getObjects()
            ->shouldBeCalled()
            ->willReturn([$object1->reveal(), $object2->reveal()]);

        $templateDataFactory->createRegistrationFromType($type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal());

        $sheet1RegistrationData = [
            'Mb7d3M765e' => [
                'path' => '/path/to/file1',
                'extension' => 'jpg',
            ],
            'Med79Mea70' => [],
        ];

        $participant = $this->prophesize(Participant::class);
        $participant->getIdAndFullName()->shouldBeCalled()->willReturn('1-jean-paul-sartre');
        $participant->getUser()->willReturn($user->reveal());
        $participant->getData()
            ->shouldBeCalled()
            ->willReturn([
                'Mb7d3M765e' => [],
                'Med79Mea70' => [
                    'path' => '/path/to/file2',
                    'extension' => 'jpg',
                ]
            ]);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getIdAndFullName()->shouldBeCalled()->willReturn('2-simone-de-beauvoir');
        $participant2->getUser()->willReturn($user2->reveal());
        $participant2->getData()
            ->shouldBeCalled()
            ->willReturn([
                'Med79Mea70' => [
                    'path' => '/path/to/file3',
                    'extension' => 'jpg',
                ]
            ]);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn('1');
        $sheet1->getTitle()->shouldBeCalled()->willReturn('Title 1');
        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet1->getRegistrationData()->shouldBeCalled()->willReturn($sheet1RegistrationData);
        $sheet1->getParticipantsArray()->shouldBeCalled()->willReturn([]);

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn('2');
        $sheet2->getTitle()->shouldBeCalled()->willReturn('Title 2');
        $sheet2->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet2->getRegistrationData()->shouldBeCalled()->willReturn([]);
        $sheet2->getParticipantsArray()->shouldBeCalled()->willReturn([$participant->reveal(), $participant2->reveal()]);

        $transliteratorAdapter->urlize([1, 'Title 1'])->shouldBeCalled()->willReturn('1-title-1');
        $transliteratorAdapter->urlize([2, 'Title 2', '1-jean-paul-sartre'])->shouldBeCalled()->willReturn('2-title-2-1-jean-paul-sartre');
        $transliteratorAdapter->urlize([2, 'Title 2', '2-simone-de-beauvoir'])->shouldBeCalled()->willReturn('2-title-2-2-simone-de-beauvoir');

        $formTemplate = $this->prophesize(FormTemplate::class);
        $formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $sheetFormDataRepository = $this->prophesize(SheetRepository\FormDataRepositoryInterface::class);
        $userFormDataRepository = $this->prophesize(UserRepository\FormDataRepositoryInterface::class);
        $formData = new Sheet\FormData($sheet1->reveal(), $formTemplate->reveal(), [
            'Med79Mea71' => [
                'path' => '/path/to/file4',
                'extension' => 'jpg',
            ]
        ]);
        $sheetFormDataRepository->getBySheetAndFormTemplate($sheet1->reveal(), $formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($formData);
        $sheetFormDataRepository->getBySheetAndFormTemplate($sheet2->reveal(), $formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $uploadObject = $this->prophesize(UploadObject::class);
        $uploadObject->hasTag(Tag::SHEET_DATA)->shouldBeCalled()->willReturn(true);
        $uploadObject->hasTag(Tag::PARTICIPANT_DATA)->shouldBeCalled()->willReturn(false);
        $uploadObject->isCrypted()->shouldBeCalled()->willReturn(false);
        $uploadObject->getKey()->shouldBeCalled()->willReturn('Med79Mea71');
        $uploadObject->getLabel('fr')->shouldBeCalled()->willReturn('Label 4');

        $formTemplateRepository->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$formTemplate->reveal()]);

        $templateData2 = $this->prophesize(TemplateData::class);
        $templateData2->getObjects()
            ->shouldBeCalled()
            ->willReturn([$uploadObject->reveal()]);

        $templateDataFactory->createFormTemplateFromTemplate($formTemplate->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData2);

        $handler = new GetUploadedObjectsTreeQueryHandler(
            $templateDataFactory->reveal(),
            $transliteratorAdapter->reveal(),
            $formTemplateRepository->reveal(),
            $sheetFormDataRepository->reveal(),
            $userFormDataRepository->reveal()
        );

        $transliteratorAdapter->urlize(['Med79Mea71', 1, 'Title 1'])->shouldBeCalled()->willReturn('Med79Mea71-1-title-1');
        $transliteratorAdapter->urlize(['Med79Mea71', 'Label 4'])->shouldBeCalled()->willReturn('Med79Mea71-1-title-1');
        $transliteratorAdapter->urlize(['Mb7d3M765e', 'Label 1'])->shouldBeCalled()->willReturn('Mb7d3M765e-label-1');

        $node1 = new UploadedObjectNodeView('Mb7d3M765e-label-1');
        $node1->addUploadedObjectView(
            new UploadedObjectView('/path/to/file1', '1-title-1.jpg', true, $sheet1->reveal(), null, true)
        );

        $transliteratorAdapter->urlize(['Med79Mea70', 'Label 2'])->shouldBeCalled()->willReturn('Med79Mea70-label-2');
        $node2 = new UploadedObjectNodeView('Med79Mea70-label-2');
        $node2->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file2',
                '2-title-2-1-jean-paul-sartre.jpg',
                false,
                $sheet2->reveal(),
                $user->reveal(),
                false
            )
        );
        $node2->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file3',
                '2-title-2-2-simone-de-beauvoir.jpg',
                false,
                $sheet2->reveal(),
                $user2->reveal(),
                false
            )
        );
        $node3 = new UploadedObjectNodeView('Med79Mea71-1-title-1');
        $node3->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file4',
                'Med79Mea71-1-title-1.jpg',
                false,
                $sheet1->reveal(),
                null,
                true
            )
        );

        $expectedResult = new UploadedObjectsTreeView();
        $expectedResult->addNode($node1, 'Mb7d3M765e');
        $expectedResult->addNode($node2, 'Med79Mea70');
        $expectedResult->addNode($node3, 'Med79Mea71');
        $result = $handler->handle(
            new GetUploadedObjectsTreeQuery(
                $event->reveal(),
                [$sheet1->reveal(), $sheet2->reveal()],
                $admin->reveal()
            )
        );

        $this->assertEquals($expectedResult, $result);
    }
}
