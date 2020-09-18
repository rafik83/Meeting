<?php


namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class UserCtaSubmenuViewQueryHandlerTest extends TestCase
{
    public function testCustomButton()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(42);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()->shouldBeCalled()->willReturn('
        {
             "link": "https://example.net/%userId%/%userEmail%",
             "concerned_type_ids": [689, 746, 747, 748, 749, 750],
             "button-label": {
             "fr": "Mon bouton",
             "en": "My button"
             }
        }');
        $extraParameterRepository->findByEventAndType($event->reveal(), Type::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn($extraParameter->reveal());

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $expectedCustomButtonSubmenuButtonView = new SubmenuButtonView(
        Category::CUSTOM_BUTTON_ICON,
            'Mon bouton',
            'https://example.net/42/test%40yahoo.fr',
            false,
            null,
            false,
            ['target' => '_blank']
    );

        $this->assertEquals($expectedCustomButtonSubmenuButtonView, $result);
    }

    public function testCustomButtonNull()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $user->getEmail()->willReturn('test@yahoo.fr');
        $event = $this->prophesize(Event::class);

        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $extraParameterRepository->findByEventAndType($event->reveal(), Type::TYPE_CUSTOM_BUTTON)->shouldBeCalled()->willReturn(null);

        $userCtaSubmenuViewQueryHandler = new UserCtaSubmenuViewQueryHandler(
            $extraParameterRepository->reveal()
        );

        $result = $userCtaSubmenuViewQueryHandler->handle(
            new UserCtaSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal()
            )
        );

        $this->assertEquals(null, $result);
    }
}
