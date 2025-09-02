<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Forms;

// Test unitaire du formulaire UserType
class UserTypeTest extends TypeTestCase
{
	// Vérifie que le formulaire contient bien les champs attendus
	public function testFormHasFields()
	{
		$form = $this->factory->create(UserType::class);
		$this->assertTrue($form->has('username'));
		$this->assertTrue($form->has('password'));
		$this->assertTrue($form->has('email'));
	}
}
