<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Contact\CanAccessToContacts;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class ContactsSubmenuViewQueryHandler
{
    /** @var NavigationBuilderInterface */
    private $navigationBuilder;

    /** @var CanAccessToContacts */
    private $canAccessToContacts;

    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        CanAccessToContacts $canAccessToContacts
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->canAccessToContacts = $canAccessToContacts;
    }

    public function handle(ContactsSubmenuViewQuery $query): ?SubmenuButtonView
    {
        if (!$this->canAccessToContacts->isSatisfiedBy($query->event, $query->user, $query->sheet)) {
            return null;
        }

        $contactTitle = 'navigation.category.contact';

        if (isset($query->staticFormulationsIndexedByCategory[Category::CONTACT_LIST])) {
            $contactTitle = $query->staticFormulationsIndexedByCategory[Category::CONTACT_LIST]->getTitle($query->locale);
        }

        return new SubmenuButtonView(
            Category::CONTACT_LIST_ICON,
            $contactTitle,
            $this->navigationBuilder->getRoute(
                'event_contact_index',
                [
                    'sheet' => $query->sheet->getId(),
                ]
            ),
            Route::isContactList($query->route),
            null,
            true
        );
    }
}
