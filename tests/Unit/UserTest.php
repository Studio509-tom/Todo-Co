<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entity\User;

// Test unitaire de l'entité User
class UserTest extends TestCase
{
    // Vérifie que l'on peut attribuer le rôle ADMIN à un utilisateur
    public function testUserRoleAssignment()
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    // Vérifie que le rôle par défaut est ROLE_USER
    public function testDefaultRoleIsUser()
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}