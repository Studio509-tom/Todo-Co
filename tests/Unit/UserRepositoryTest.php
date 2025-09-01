<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

// Test unitaire du repository User
class UserRepositoryTest extends TestCase
{
	// Vérifie que le repository peut être instancié correctement
	public function testRepositoryCanBeInstantiated()
	{
		$mockRegistry = $this->createMock(ManagerRegistry::class);
		$repo = new UserRepository($mockRegistry);
		$this->assertInstanceOf(UserRepository::class, $repo);
	}
	
}
