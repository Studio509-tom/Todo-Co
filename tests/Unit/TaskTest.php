<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entity\Task;
use App\Entity\User;

// Test unitaire de l'entité Task
class TaskTest extends TestCase
{
    // Vérifie qu'une tâche peut être associée à un utilisateur
    public function testTaskIsAttachedToUser()
    {
        $user = new User();
        $task = new Task();
        $task->setAuthor($user);
        $this->assertSame($user, $task->getAuthor());
    }
}