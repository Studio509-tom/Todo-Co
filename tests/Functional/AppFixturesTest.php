<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Task;
use App\DataFixtures\AppFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
/**
 * Test fonctionnel pour vérifier que les données de la fixture générale AppFixtures
 * sont bien présentes en base après chargement.
 */
class AppFixturesTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $em;
    
    private $databaseTool;
    /**
     * Initialise le kernel Symfony et récupère l'EntityManager avant chaque test.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();

        $databaseToolCollection = self::getContainer()->get(DatabaseToolCollection::class);
        $this->databaseTool = $databaseToolCollection->get();
        $this->databaseTool->loadFixtures([AppFixtures::class]);
    }

    /**
     * Vérifie que l'utilisateur admin existe et possède le rôle ROLE_ADMIN.
     */
    public function testAdminUserExists()
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@todoandco.com']);
        $this->assertNotNull($admin);
        $this->assertContains('ROLE_ADMIN', $admin->getRoles());
    }

    /**
     * Vérifie que l'utilisateur anonyme existe et possède le rôle ROLE_USER.
     */
    public function testAnonymousUserExists()
    {
        $anonymous = $this->em->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        $this->assertNotNull($anonymous);
        $this->assertContains('ROLE_USER', $anonymous->getRoles());
    }

    /**
     * Vérifie que l'admin possède au moins 3 tâches associées.
     */
    public function testAdminTasksExist()
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@todoandco.com']);
        $tasks = $this->em->getRepository(Task::class)->findBy(['author' => $admin]);
        $this->assertGreaterThanOrEqual(3, count($tasks));
    }

    /**
     * Vérifie que l'utilisateur anonyme possède au moins 2 tâches associées.
     */
    public function testAnonymousTasksExist()
    {
        $anonymous = $this->em->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        $tasks = $this->em->getRepository(Task::class)->findBy(['author' => $anonymous]);
        $this->assertGreaterThanOrEqual(2, count($tasks));
    }

    /**
     * Vérifie que chaque utilisateur classique possède au moins 2 tâches associées.
     */
    public function testUserTasksExist()
    {
        for ($i = 1; $i <= 3; $i++) {
            $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'user'.$i]);
            $this->assertNotNull($user);
            $tasks = $this->em->getRepository(Task::class)->findBy(['author' => $user]);
            $this->assertGreaterThanOrEqual(2, count($tasks));
        }
    }
}