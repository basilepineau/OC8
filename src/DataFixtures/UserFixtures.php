<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\RoleEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user-';
    public const ADMIN_REFERENCE = 'admin-';
    public const ANONYMOUS_REFERENCE = 'anonymous-user';

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Utilisateur anonyme
        $anonymous = new User();
        $anonymous->setUsername('anonyme');
        $anonymous->setEmail('anonyme@example.com');
        $anonymous->setRoles([RoleEnum::ANONYMOUS]);
        $anonymous->setPassword($this->hasher->hashPassword($anonymous, 'anonyme'));
        $manager->persist($anonymous);
        $this->addReference(self::ANONYMOUS_REFERENCE, $anonymous);

        // 3 utilisateurs normaux
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setUsername("user$i");
            $user->setEmail("user$i@example.com");
            $user->setRoles([RoleEnum::USER]);
            $user->setPassword($this->hasher->hashPassword($user, "password$i"));
            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE.$i, $user);
        }

        // 3 administrateurs
        for ($i = 1; $i <= 3; $i++) {
            $admin = new User();
            $admin->setUsername("admin$i");
            $admin->setEmail("admin$i@example.com");
            $admin->setRoles([RoleEnum::ADMIN]);
            $admin->setPassword($this->hasher->hashPassword($admin, "adminpass$i"));
            $manager->persist($admin);
            $this->addReference(self::ADMIN_REFERENCE.$i, $admin);
        }

        $manager->flush();
    }
}
