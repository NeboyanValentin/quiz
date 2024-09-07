<?php

namespace App\Entity;

use App\Repository\UserAnswerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserAnswerRepository::class)]
class UserAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $userUuid;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'userAnswers')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id')]
    private Question $question;

    #[ORM\ManyToOne(targetEntity: Answer::class, inversedBy: 'userAnswers')]
    #[ORM\JoinColumn(name: 'answer_id', referencedColumnName: 'id')]
    private Answer $answer;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Uuid|null
     */
    public function getUserUuid(): ?Uuid
    {
        return $this->userUuid;
    }

    /**
     * @param Uuid|null $userUuid
     */
    public function setUserUuid(?Uuid $userUuid): void
    {
        $this->userUuid = $userUuid;
    }

    /**
     * @return Question
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * @param Question $question
     */
    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    /**
     * @return Answer
     */
    public function getAnswer(): Answer
    {
        return $this->answer;
    }

    /**
     * @param Answer $answer
     */
    public function setAnswer(Answer $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeImmutable|null $createdAt
     */
    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
