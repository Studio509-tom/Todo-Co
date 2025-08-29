<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: "user")]
#[ORM\Entity]
#[UniqueEntity("email")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Identifiant unique de l'utilisateur
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;
    
    // Nom d'utilisateur
    #[ORM\Column(type: "string", length: 25, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private string $username;

    // Rôles de l'utilisateur (ROLE_USER, ROLE_ADMIN...)
    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un rôle.')]
    private array $roles = ['ROLE_USER'];

    // Mot de passe hashé
    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Vous devez saisir un mot de passe.")]
    private string $password;


    // Email de l'utilisateur
    #[ORM\Column(type: "string", length: 60, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir une adresse email.")]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    private string $email;

    // ...getters/setters obligatoires pour UserInterface
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    // public function getSalt()
    // {
    //     return null;
    // }
    public function getRoles(): array
    {
        // Garantit que chaque utilisateur a au moins ROLE_USER
        $roles = $this->roles;
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

     // Définit les rôles de l'utilisateur
    public function setRoles( $roles): self
    {
        $this->roles = is_array($roles) ? $roles : [$roles];
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }
    public function eraseCredentials(): void
    {
        // nettoyage de données sensibles ici si nécessaire
    }
    

}
