<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Form\UserType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Forms;

// Test unitaire du formulaire UserType
class UserTypeTest extends TestCase
{
	// Vérifie que le formulaire contient bien les champs attendus
	public function testFormHasFields()
	{
		$formFactory = Forms::createFormFactoryBuilder()
			->addExtensions([new PreloadedExtension([new UserType()], [])])
			->getFormFactory();
		$form = $formFactory->create(UserType::class);
		$this->assertTrue($form->has('username'));
		$this->assertTrue($form->has('password'));
		$this->assertTrue($form->has('email'));
	}
}
