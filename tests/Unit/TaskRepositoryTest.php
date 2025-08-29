<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;

// Test unitaire du repository Task
class TaskRepositoryTest extends TestCase
{
	// Vérifie que le repository peut être instancié correctement
	public function testRepositoryCanBeInstantiated()
	{
		$mockRegistry = $this->createMock(ManagerRegistry::class);
		$repo = new TaskRepository($mockRegistry);
		$this->assertInstanceOf(TaskRepository::class, $repo);
	}
}
