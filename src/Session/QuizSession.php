<?php

namespace App\Session;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

class QuizSession
{
    const QUIZ_SESSION_UUID_NAME = 'quiz_session_uuid';

    public function __construct(
        private readonly RequestStack $requestStack,
    )
    {

    }

    public function getUuid(): UuidV4
    {
        $uuid = $this->requestStack->getSession()->get(self::QUIZ_SESSION_UUID_NAME);

        if (!$uuid) {
            $uuid = Uuid::v4();
            $this->requestStack->getSession()->set(self::QUIZ_SESSION_UUID_NAME, $uuid);
        }

        return $uuid;
    }

    public function resetUuid(): void
    {
        $uuid = Uuid::v4();
        $this->requestStack->getSession()->set(self::QUIZ_SESSION_UUID_NAME, $uuid);
    }

    public function getUuidString(): string
    {
        return $this->getUuid()->toString();
    }
}