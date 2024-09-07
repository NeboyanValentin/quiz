<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\UserAnswer;
use App\Session\QuizSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;


class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function getNextRandomQuestion(QuizSession $quizSession)
    {
        $questions = $this->createQueryBuilder('q')
            ->select('q')
            ->leftJoin(UserAnswer::class, 'ua', 'WITH', 'ua.question = q.id AND ua.userUuid = :uuid')
            ->where('ua.id IS NULL')
            ->setParameter('uuid', $quizSession->getUuidString())
            ->getQuery()
            ->getResult();

        return current($questions);
    }

    /**
     * @throws Exception
     */
    public function getUserAnswers(QuizSession $quizSession): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "SELECT  q.id AS question_id, 
                q.text AS question_text, 
                JSON_AGG(JSON_BUILD_OBJECT(
                'answer_id', a.id,
                'answer_text', a.text,
                'is_correct', a.is_correct,
                'is_selected', CASE WHEN ua.answer_id IS NOT NULL THEN TRUE ELSE FALSE END 
                         )) AS user_answers
                FROM question q
                JOIN answer a ON a.question_id = q.id
                LEFT JOIN user_answer ua ON ua.answer_id = a.id AND ua.question_id = q.id AND ua.user_uuid = :uuid
                GROUP BY q.id;";

        return $connection->executeQuery($sql, ['uuid' => $quizSession->getUuidString()])->fetchAllAssociative();
    }
}
