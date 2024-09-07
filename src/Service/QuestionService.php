<?php

namespace App\Service;

use App\Entity\Answer;
use App\Entity\UserAnswer;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\UserAnswerRepository;
use App\Session\QuizSession;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class QuestionService
{

    public function __construct(
        protected QuestionRepository     $questionRepository,
        protected AnswerRepository       $answerRepository,
        protected UserAnswerRepository   $userAnswerRepository,
        protected EntityManagerInterface $em,
    )
    {
    }

    public function getQuestion(QuizSession $quizSession)
    {
        return $this->questionRepository->getNextRandomQuestion($quizSession);
    }

    /**
     * @throws Exception
     */
    public function getUserQuestions(QuizSession $quizSession): array
    {
        $userQuestionResult = $this->questionRepository->getUserAnswers($quizSession);

        (new ArrayCollection($userQuestionResult))
            ->map(function ($userAnswerData) use (&$correctAnswered, &$inCorrectAnswered) {
                $userAnswerData['user_answers'] = json_decode($userAnswerData['user_answers'], TRUE);
                $isCorrectAnswered = TRUE;

                foreach ($userAnswerData['user_answers'] as $userAnswer) {
                    if ($userAnswer['is_selected'] === FALSE) {
                        continue;
                    }

                    if ($userAnswer['is_correct'] === FALSE) {
                        $isCorrectAnswered = FALSE;
                        break;
                    }
                }

                if ($isCorrectAnswered === FALSE) {
                    $inCorrectAnswered[] = $userAnswerData;
                    return TRUE;
                }

                $correctAnswered[] = $userAnswerData;
            });

        return [$correctAnswered, $inCorrectAnswered];
    }

    public function saveUserAnswer(QuizSession $quizSession, $questionId, $userAnswerIds): void
    {
        $question = $this->questionRepository->findOneBy(['id' => $questionId]);
        $userAnswers = new ArrayCollection($this->answerRepository->findBy(['id' => $userAnswerIds]));

        $userAnswers->map(function (Answer $answer) use ($quizSession, $question, $userAnswerIds) {
            $userAnswer = new UserAnswer();
            $userAnswer->setQuestion($question);
            $userAnswer->setAnswer($answer);
            $userAnswer->setUserUuid($quizSession->getUuid());
            $userAnswer->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($userAnswer);
            $this->em->flush();
        });
    }
}