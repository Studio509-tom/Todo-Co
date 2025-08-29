<?php


namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test fonctionnel du contrôleur par défaut (page d'accueil et environnement)
class DefaultControllerTest extends WebTestCase
{
    // Vérifie que la page d'accueil est accessible en environnement de test
    #[Test]
    public function testHomePageIsSuccessful()
    {
        $this->assertSame('test', $_ENV['APP_ENV']);
    }

    // Vérifie que la variable d'environnement MON_ENV_TEST est bien chargée
    #[Test]
    public function testEnvTestIsLoaded()
    {
        $this->assertEquals('oui', $_ENV['MON_ENV_TEST']);
    }
}