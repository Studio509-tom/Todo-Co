<?php

namespace Tests\Unit;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
	public function testLoginInvalidCredentials()
	{
		$client = static::createClient();

    // Accès à la page de connexion
    $crawler = $client->request('GET', '/login');
    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('form');

    // Soumission du formulaire avec mauvais identifiants
    $form = $crawler->selectButton('Se connecter')->form([
        '_username' => 'utilisateur_inexistant@test.com',
        '_password' => 'mauvaismotdepasse',
    ]);
    $client->submit($form);

   	$this->assertStringContainsString('Invalid credentials', $client->getResponse()->getContent());
	}

  // public function testLoginWithValidCredentials()
  //   {
  //     $client = static::createClient();

  //     $crawler = $client->request('GET', '/login');

  //     $form = $crawler->selectButton('Se connecter')->form([
  //         '_username' => 'test@test.test',
  //         '_password' => 'test',
  //     ]);

  //     $client->submit($form);
  //     $this->assertSelectorTextContains('body', 'Se déconnecter');

  //   }

}