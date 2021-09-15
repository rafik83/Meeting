<?php

namespace Application\Query\User\Event\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserContactEvaluationRow;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserContactEvaluationView;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserContactEvaluationViewQuery;
use Proximum\Vimeet\Application\Query\User\Event\Contact\UserContactEvaluationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UserContactEvaluationViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);

        $contactRow1 = $this->makeRow(1, 2);
        $contactRow2 = $this->makeRow(3, 4);

        $contactRepository->getEvaluationsByEvent($event->reveal(), 'fr')->shouldBeCalled()->willReturn([$contactRow1, $contactRow2]);

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesAndCategoriesTranslationsByEvent($event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                $this->makeType(5, 'Exposant', ['Exposants']),
                $this->makeType(6, 'Acheteur', ['Big compagnies', 'Buyers'])
            ]);

        $userContactEvaluationViewQueryHandler = new UserContactEvaluationViewQueryHandler(
            $contactRepository->reveal(),
            $typeRepository->reveal()
        );

        $expectedViews = [
            new UserContactEvaluationView($contactRow1, 'Exposant', 'Exposants', 'Acheteur', 'Big compagnies, Buyers'),
            new UserContactEvaluationView($contactRow2, 'Exposant', 'Exposants', 'Acheteur', 'Big compagnies, Buyers'),
        ];

        $result = $userContactEvaluationViewQueryHandler->handle(new UserContactEvaluationViewQuery($event->reveal(), 'fr'));

        $this->assertEquals($expectedViews, $result);
    }

    private function makeRow(int $userId, int $evaluatedUserId): UserContactEvaluationRow
    {

        return new UserContactEvaluationRow(
            $userId,
            'First Name '.$userId,
            'Last Name '.$userId,
            1,
            'Aanera',
            5,
            2,
            123,
            $evaluatedUserId,
            'First Name'.$evaluatedUserId,
            'Last Name '.$evaluatedUserId,
            4,
            'World company',
            6
        );
    }

    private function makeType(int $id, string $title, array $categories): Type
    {
        $type = $this->prophesize(Type::class);
        $type->getId()->willReturn($id);
        $type->getTitle('fr')->willReturn($title);
        $type->getCategoriesTitles('fr')->willReturn($categories);

        return $type->reveal();
    }
}
