<?php

use App\Entity\Task;
use App\Entity\User;
use App\Enum\RoleEnum;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserName()
    {
        $user = new User();
        $user->setUsername('basile');
        $this->assertEquals('basile', $user->getUsername());
        $this->assertEquals('basile', $user->getUserIdentifier());
    }

        public function testPassword()
    {
        $user = new User();
        $user->setPassword('secret');
        $this->assertEquals('secret', $user->getPassword());
    }

    public function testEmail()
    {
        $user = new User();
        $user->setEmail('basile@orange.fr');
        $this->assertEquals('basile@orange.fr', $user->getEmail());
    }

    public function testRolesDefault()
    {
        $user = new User();
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testSetRoles()
    {
        $user = new User();
        $user->setRoles([RoleEnum::USER, RoleEnum::ADMIN, RoleEnum::USER]);
        $roles = $user->getRoles();
        $this->assertContains(RoleEnum::USER->value, $roles);
        $this->assertContains(RoleEnum::ADMIN->value, $roles);
        $this->assertCount(2, $roles);
    }

    public function testAddRole()
    {
        $user = new User();
        $user->addRole(RoleEnum::ADMIN);
        $this->assertContains(RoleEnum::ADMIN->value, $user->getRoles());
        $user->addRole(RoleEnum::USER);
        $this->assertContains(RoleEnum::USER->value, $user->getRoles());
        // Test unicité
        $user->addRole(RoleEnum::ADMIN);
        $roles = $user->getRoles();
        $this->assertCount(2, $roles);
    }

    public function testRemoveRole()
    {
        $user = new User();
        $user->setRoles([RoleEnum::USER, RoleEnum::ADMIN]);
        $user->removeRole(RoleEnum::ADMIN);
        $this->assertNotContains(RoleEnum::ADMIN->value, $user->getRoles());
        $this->assertContains(RoleEnum::USER->value, $user->getRoles());
    }

    public function testSetSingleRole()
    {
        $user = new User();
        $user->setSingleRole(RoleEnum::ADMIN);
        $roles = $user->getRoles();
        $this->assertEquals([RoleEnum::ADMIN->value], $roles);
    }

    public function testGetRoleLabels()
    {
        $user = new User();
        $user->setRoles([RoleEnum::USER, RoleEnum::ADMIN]);
        $labels = $user->getRoleLabels();
        $this->assertContains(RoleEnum::USER->getLabel(), $labels);
        $this->assertContains(RoleEnum::ADMIN->getLabel(), $labels);
    }

    public function testAddTask()
    {
        $user = new User();
        $task = new Task();

        $user->addTask($task);

        $this->assertTrue($user->getTasks()->contains($task));
        $this->assertSame($user, $task->getUser());
    }

    public function testRemoveTask()
    {
        $user = new User();
        $task = new Task();
        $task->setUser($user);

        $user->addTask($task);
        $this->assertTrue($user->getTasks()->contains($task));

        $user->removeTask($task);
        $this->assertFalse($user->getTasks()->contains($task));
        $this->assertNull($task->getUser());
    }
}