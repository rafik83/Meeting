<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\FormData as SheetFormData;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\FormData as UserFormData;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Repository\Sheet as SheetRepository;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class FormTemplateDataQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $templateDataFactory,
        $sheetFormDataRepository,
        $userFormDataRepository,
        $sheet,
        $participant,
        $user,
        $formTemplate
    ;

    public function setUp()
    {
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->sheetFormDataRepository = $this->prophesize(SheetRepository\FormDataRepositoryInterface::class);
        $this->userFormDataRepository = $this->prophesize(UserRepository\FormDataRepositoryInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->user = $this->prophesize(User::class);
        $this->formTemplate = $this->prophesize(FormTemplate::class);

        $this->participant->getUser()->shouldBeCalled()->willReturn($this->user->reveal());
    }

    public function testHandleWithoutUserFormData()
    {
        $sheetFormData = new SheetFormData($this->sheet->reveal(), $this->formTemplate->reveal(), ['toto' => ['text' => 'tata']]);
        $this->sheetFormDataRepository
            ->getBySheetAndFormTemplate($this->sheet->reveal(), $this->formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetFormData)
        ;

        $this->userFormDataRepository
            ->getByUserAndFormTemplate($this->user->reveal(), $this->formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $templateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createFormTemplateWithData($this->formTemplate->reveal(), ['toto' => ['text' => 'tata']], 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $query = new FormTemplateDataQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr'
        );

        $handler = new FormTemplateDataQueryHandler(
            $this->templateDataFactory->reveal(),
            $this->sheetFormDataRepository->reveal(),
            $this->userFormDataRepository->reveal()
        );

        $result = $handler->handle($query);
    }

    public function testHandle()
    {
        $sheetFormData = new SheetFormData($this->sheet->reveal(), $this->formTemplate->reveal(), ['toto' => ['text' => 'tata']]);
        $this->sheetFormDataRepository
            ->getBySheetAndFormTemplate($this->sheet->reveal(), $this->formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetFormData)
        ;

        $userFormData = new UserFormData($this->user->reveal(), $this->formTemplate->reveal(), ['titi' => ['text' => 'tutu']]);
        $this->userFormDataRepository
            ->getByUserAndFormTemplate($this->user->reveal(), $this->formTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($userFormData)
        ;

        $templateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createFormTemplateWithData($this->formTemplate->reveal(), ['toto' => ['text' => 'tata'], 'titi' => ['text' => 'tutu']], 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;

        $query = new FormTemplateDataQuery(
            $this->formTemplate->reveal(),
            $this->sheet->reveal(),
            $this->participant->reveal(),
            'fr'
        );

        $handler = new FormTemplateDataQueryHandler(
            $this->templateDataFactory->reveal(),
            $this->sheetFormDataRepository->reveal(),
            $this->userFormDataRepository->reveal()
        );

        $result = $handler->handle($query);
    }
}
