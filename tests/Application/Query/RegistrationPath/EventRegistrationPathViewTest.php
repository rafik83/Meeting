<?php

namespace Proximum\Vimeet\Tests\Application\Query\RegistrationPath;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\RegistrationPath\AnswerView;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Proximum\Vimeet\Application\Query\RegistrationPath\TypeView;

class EventRegistrationPathViewTest extends TestCase
{
    public function test_path_is_completed_because_it_is_empty()
    {
        $eventRegistrationPathView = new EventRegistrationPathView(null);

        $this->assertTrue($eventRegistrationPathView->isPathCompleted());
    }

    public function test_path_is_completed()
    {
        $typeView1 = new TypeView(42, 'Exhibitor');
        $typeView2 = new TypeView(1242, 'Visitor');

        $firstQuestionView = new QuestionView(
            1,
            'Do you do meetings?',
            []
        );

        $secondQuestionView = new QuestionView(
            2,
            'Where do you come from?',
            []
        );
        $secondQuestionView->answerViews = [
            new AnswerView($secondQuestionView, 101, 'Paris', [$typeView1]),
            new AnswerView($secondQuestionView, 102, 'London', []), // missing next step
            new AnswerView($secondQuestionView, 103, 'New York', [$typeView1, $typeView2]),
        ];

        $answerView1 = new AnswerView($firstQuestionView, 22, 'Yes', []);
        $answerView1->setNextQuestionView($secondQuestionView);
        $answerView2 = new AnswerView($firstQuestionView, 23, 'No', [$typeView1]);

        $firstQuestionView->answerViews = [
            $answerView1,
            $answerView2,
        ];

        $eventRegistrationPathView = new EventRegistrationPathView($firstQuestionView);

        $this->assertFalse($eventRegistrationPathView->isPathCompleted());
    }

    public function test_path_is_not_completed()
    {
        $typeView1 = new TypeView(42, 'Exhibitor');
        $typeView2 = new TypeView(1242, 'Visitor');

        $secondQuestionView = new QuestionView(
            2,
            'Where do you come from?',
            []
        );
        $secondQuestionView->answerViews = [
            new AnswerView($secondQuestionView, 101, 'Paris', [$typeView1, $typeView2]),
            new AnswerView($secondQuestionView, 102, 'London', [$typeView2]),
            new AnswerView($secondQuestionView, 103, 'New York', [$typeView1, $typeView2]),
        ];


        $firstQuestionView = new QuestionView(
            1,
            'Do you do meetings?',
            []
        );

        $answerView1 = new AnswerView($firstQuestionView, 22, 'Yes', []); // no types but a next question is assigned
        $answerView1->setNextQuestionView($secondQuestionView);

        $answerView2 = new AnswerView($firstQuestionView, 23, 'No', [$typeView1]);

        $firstQuestionView->answerViews = [$answerView1, $answerView2];

        $eventRegistrationPathView = new EventRegistrationPathView($firstQuestionView);

        $this->assertTrue($eventRegistrationPathView->isPathCompleted());
    }
}
