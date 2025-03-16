<?php

namespace App\Entity;

use App\Enum\RoleEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table(name: 'user')]
#[ORM\Entity]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez saisir un nom d\'utilisateur.')]
    private string $username;

    #[ORM\Column(type: 'string', length: 64)]
    private string $password;

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
    #[Assert\Email(message: 'Le format de l\'adresse n\'est pas correcte.')]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];    

    public function getId()
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return empty($this->roles) ? ['ROLE_USER'] : $this->roles;
    }
    
    public function setRoles(array $roles): static
    {
        $this->roles = array_unique(array_map(fn(RoleEnum $role) => $role->value, $roles));
    
        return $this;
    }
    
    public function addRole(RoleEnum $role): static
    {
        if (!in_array($role->value, $this->roles, true)) {
            $this->roles[] = $role->value;
        }
    
        return $this;
    }
    
    public function removeRole(RoleEnum $role): static
    {
        $this->roles = array_filter($this->roles, fn(string $r) => $r !== $role->value);
    
        return $this;
    }    
    
    public function setSingleRole(RoleEnum $role): static
    {
        $this->roles = [$role->value];

        return $this;
    }

    public function getRoleLabels(): array
    {
        return array_map(fn(string $role) => RoleEnum::from($role)->getLabel(), $this->roles);
    }    

}
