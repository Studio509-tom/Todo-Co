<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Form\TaskType;
use Symfony\Component\Form\Test\TypeTestCase;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Forms;

// Test unitaire du formulaire TaskType
class TaskTypeTest extends TypeTestCase
{
	// Vérifie que le formulaire contient bien les champs attendus
	public function testFormHasFields()
	{
		$form = $this->factory->create(TaskType::class);
		$this->assertTrue($form->has('title'));
		$this->assertTrue($form->has('content'));
	}
}
