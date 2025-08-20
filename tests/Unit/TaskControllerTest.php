<?php

namespace App\Tests\Unit;

use App\Entity\Task;
use App\Controller\TaskController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use DateTimeImmutable;

class TaskControllerTest extends TestCase
{
	public function testTaskEntity(): void
    {
        $task = new Task();
        $title = 'Titre de test';
        $content = 'Contenu de la tâche';
        $createdAt = new DateTimeImmutable();

        $task->setTitle($title);
        $task->setContent($content);
        $task->setCreatedAt($createdAt);

        $this->assertEquals($title, $task->getTitle());
        $this->assertEquals($content, $task->getContent());
        $this->assertEquals($createdAt, $task->getCreatedAt());
    }
}