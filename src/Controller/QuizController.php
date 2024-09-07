<?php

namespace App\Controller;

use App\Service\QuestionService;
use App\Session\QuizSession;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuizController extends AbstractController
{
    public function __construct(
        private readonly QuestionService $questionService,
    )
    {
    }

    #[Route('/', name: 'app_quiz')]
    public function index(): Response
    {
        return $this->render('quiz/index.html.twig');
    }

    #[Route('/quiz/start', name: 'app_quiz_start')]
    public function start(QuizSession $quizSession): Response
    {
        $quizSession->resetUuid();

        return $this->redirectToRoute('app_quiz_question');
    }

    /**
     * @throws Exception
     */
    #[Route('/quiz/result', name: 'app_quiz_result')]
    public function result(QuizSession $quizSession): Response
    {
        list($correctAnsweredQuestions, $inCorrectAnsweredQuestions) = $this->questionService->getUserQuestions($quizSession);

        return $this->render('quiz/result.html.twig', compact('correctAnsweredQuestions', 'inCorrectAnsweredQuestions'));
    }

    #[Route('/quiz/question', name: 'app_quiz_question', methods: ['GET'])]
    public function next(QuizSession $quizSession): Response
    {
        $question = $this->questionService->getQuestion($quizSession);

        if (!$question) {
            return $this->redirectToRoute('app_quiz_result');
        }

        return $this->render('quiz/question.html.twig', compact('question'));
    }

    #[Route('/quiz/answer', name: 'app_quiz_answer', methods: ['POST'])]
    public function answer(Request $request, QuizSession $quizSession): RedirectResponse
    {
        if (empty($request->get('user_answers'))) {
            $this->addFlash('notice', 'Answer not selected!');

            return $this->redirectToRoute('app_quiz_question');
        }

        $userAnswerIds = $request->get('user_answers');
        $questionId = $request->get('question');
        $this->questionService->saveUserAnswer($quizSession, $questionId, $userAnswerIds);

        if (!$this->questionService->getQuestion($quizSession)) {
            return $this->redirectToRoute('app_quiz_result');
        }

        return $this->redirectToRoute('app_quiz_question');
    }
}
