<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 20 tâches associées à différents utilisateurs
        for ($i = 1; $i <= 20; $i++) {
            $task = new Task();
            $task->setTitle("Tâche $i");
            $task->setContent("Contenu de la tâche $i");
            $task->toggle(rand(0, 1) === 1);

            // Attribution aléatoire à user/admin/anonyme
            $type = rand(1, 3);
            if ($type === 1) {
                $userId = rand(1, 3);
                $task->setUser($this->getReference(UserFixtures::USER_REFERENCE.$userId, User::class));
            } elseif ($type === 2) {
                $adminId = rand(1, 3);
                $task->setUser($this->getReference(UserFixtures::ADMIN_REFERENCE.$adminId, User::class));
            } else {
                $task->setUser($this->getReference(UserFixtures::ANONYMOUS_REFERENCE, User::class)); // tâche anonyme
            }

            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
