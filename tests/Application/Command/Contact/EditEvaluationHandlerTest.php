<?php

namespace Proximum\Vimeet\Tests\Application\Command\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluation;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluationHandler;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EditEvaluationHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // prepare data
        $contact = $this->prophesize(Contact::class);

        // prophecies dependencies
        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $contact->setEvaluation(2)
            ->shouldBeCalled()
        ;
        $contactRepository->set($contact)->shouldBeCalled();

        // run tests
        $query = new EditEvaluation($contact->reveal(), 2);

        $editCommentHandler = new EditEvaluationHandler($contactRepository->reveal());
        $editCommentHandler->handle($query);
    }
}
