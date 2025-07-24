<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\RoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $taskRepository;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->taskRepository = $this->em->getRepository(Task::class);
        $this->userRepository = $this->em->getRepository(User::class);
        $this->cleanTestUsers();
        $this->createTestUsers();
    }

    public function testTaskList(): void
    {
        $this->loginUserByEmail('test@user.com');

        $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.btn.btn-info', 'Créer une tâche');
    }

    public function testTaskCreate(): void
    {
        $this->loginUserByEmail('test@user.com');

        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Test title',
            'task[content]' => 'Test content',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a été bien été ajoutée.');
    }

    public function testEditTaskAllowedForOwner(): void
    {
        $this->loginUserByEmail('test@user.com');

        $task = $this->createTaskForUser(
            $this->userRepository->findOneBy(['email' => 'test@user.com']),
            'Edit me',
            'Before edit'
        );

        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Edited title',
            'task[content]' => 'After edit'
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été modifiée.');
    }

    public function testEditTaskDeniedForNonOwner(): void
    {
        $owner = $this->userRepository->findOneBy(['email' => 'notme@user.com']);

        $task = $this->createTaskForUser(
            $owner,
            'Blocked edit',
            'You shall not edit'
        );

        $this->loginUserByEmail('test@user.com');

        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert');
    }

    public function testToggleTask(): void
    {
        $this->loginUserByEmail('test@user.com');

        $task = $this->createTaskForUser(
            $this->userRepository->findOneBy(['email' => 'test@user.com']),
            'Toggle task',
            'Test toggle',
            false
        );

        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertResponseRedirects('/tasks');
    }

    public function testDeleteTask(): void
    {
        $this->loginUserByEmail('test@user.com');

        $task = $this->createTaskForUser(
            $this->userRepository->findOneBy(['email' => 'test@user.com']),
            'Delete task',
            'Test delete'
        );

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
    }

    public function testCannotDeleteTaskIfNotOwner(): void
    {
        $owner = $this->userRepository->findOneBy(['email' => 'owner@user.com']);

        $task = $this->createTaskForUser(
            $owner,
            'Not my task',
            'Should not delete'
        );

        $this->loginUserByEmail('test@user.com');

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert');
    }

    public function testAdminCanDeleteAnonymousTask(): void
    {
        $anonymous = $this->userRepository->findOneBy(['email' => 'anon@anon.com']);

        $task = $this->createTaskForUser(
            $anonymous,
            'Anonymous task',
            'Admin should delete'
        );

        $this->loginUserByEmail('admin@user.com');

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
    }

    public function testCannotDeleteAnonymousTaskIfNotAdmin(): void
    {
        $anon = $this->userRepository->findOneBy(['email' => 'anon@anon.com']);

        $task = $this->createTaskForUser(
            $anon,
            'Task anonymous',
            'Should fail delete'
        );

        $this->loginUserByEmail('test@user.com');

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert', 'Seuls les administrateurs peuvent supprimer une tâche');
    }

    private function loginUserByEmail(string $email): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->client->loginUser($user);
        return $user;
    }

    private function createTestUsers(): void
    {
        $usersData = [
            ['username' => 'Test user', 'email' => 'test@user.com', 'role' => RoleEnum::USER],
            ['username' => 'Owner user', 'email' => 'owner@user.com', 'role' => RoleEnum::USER],
            ['username' => 'Admin user', 'email' => 'admin@user.com', 'role' => RoleEnum::ADMIN],
            ['username' => 'Anonymous user', 'email' => 'anon@anon.com', 'role' => RoleEnum::ANONYMOUS],
            ['username' => 'Not me', 'email' => 'notme@user.com', 'role' => RoleEnum::USER],
        ];

        foreach ($usersData as $userData) {
            $existingUser = $this->userRepository->findOneBy(['email' => $userData['email']]);
            if (!$existingUser) {
                $user = new User();
                $user->setUsername($userData['username']);
                $user->setEmail($userData['email']);
                $user->setPassword('password');
                $user->setRoles([$userData['role']]);
                $this->em->persist($user);
            }
        }

        $this->em->flush();
    }

    private function cleanTestUsers(): void
    {
        $emails = ['test@user.com', 'owner@user.com', 'admin@user.com', 'anon@anon.com', 'notme@user.com'];
        foreach ($emails as $email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if ($user) {
                foreach ($user->getTasks() as $task) {
                    $this->em->remove($task);
                }
                $this->em->remove($user);
            }
        }
        $this->em->flush();
    }

    private function createTaskForUser(User $user, string $title, string $content = '', bool $isDone = false): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setContent($content);
        $task->setUser($user);
        $task->setIsDone($isDone);

        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }
}