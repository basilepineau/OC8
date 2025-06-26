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
        $this->ensureTestUserExists();
    }

    public function testTaskList(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a.btn.btn-info', 'Créer une tâche');
    }

    public function testTaskCreate(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

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

    public function testToggleTask(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Toggle task');
        $task->setContent('Test toggle');
        $task->setUser($user);
        $task->setIsDone(false);
        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');
        $this->assertResponseRedirects('/tasks');
    }

    public function testDeleteTask(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Delete task');
        $task->setContent('Test delete');
        $task->setUser($user);
        $this->em->persist($task);
        $this->em->flush();

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
    }

    public function testCannotDeleteTaskIfNotOwner(): void
    {
        $owner = new User();
        $owner->setUsername('Owner user');
        $owner->setEmail('owner@user.com');
        $owner->setPassword('password');
        $owner->setRoles([RoleEnum::USER]);
        $this->em->persist($owner);

        $task = new Task();
        $task->setTitle('Not my task');
        $task->setContent('Should not delete');
        $task->setUser($owner);
        $this->em->persist($task);

        $this->em->flush();

        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert');
    }

    public function testAdminCanDeleteAnonymousTask(): void
    {
        $anonymous = new User();
        $anonymous->setUsername('Anonymous user');
        $anonymous->setEmail('anon@anon.com');
        $anonymous->setPassword('password');
        $anonymous->setRoles([RoleEnum::ANONYMOUS]);
        $this->em->persist($anonymous);

        $task = new Task();
        $task->setTitle('Anonymous task');
        $task->setContent('Admin should delete');
        $task->setUser($anonymous);
        $this->em->persist($task);

        $admin = new User();
        $admin->setUsername('Admin user');
        $admin->setEmail('admin@user.com');
        $admin->setPassword('password');
        $admin->setRoles([RoleEnum::ADMIN]);
        $this->em->persist($admin);

        $this->em->flush();

        $this->client->loginUser($admin);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été supprimée.');
    }

    public function testCannotDeleteAnonymousTaskIfNotAdmin(): void
    {
        $anon = new User();
        $anon->setUsername('Anon');
        $anon->setEmail('anon@anon.com');
        $anon->setPassword('password');
        $anon->setRoles([RoleEnum::ANONYMOUS]);
        $this->em->persist($anon);

        $task = new Task();
        $task->setTitle('Task anonymous');
        $task->setContent('Should fail delete');
        $task->setUser($anon);
        $this->em->persist($task);

        $this->em->flush();

        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert', 'Seuls les administrateurs peuvent supprimer une tâche');
    }

    public function testEditTaskAllowedForOwner(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $task = new Task();
        $task->setTitle('Edit me');
        $task->setContent('Before edit');
        $task->setUser($user);
        $this->em->persist($task);
        $this->em->flush();

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
        $owner = new User();
        $owner->setUsername('Not me');
        $owner->setEmail('notme@user.com');
        $owner->setPassword('password');
        $owner->setRoles([RoleEnum::USER]);
        $this->em->persist($owner);

        $task = new Task();
        $task->setTitle('Blocked edit');
        $task->setContent('You shall not edit');
        $task->setUser($owner);
        $this->em->persist($task);
        $this->em->flush();

        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);
        $this->client->loginUser($user);

        $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert');
    }

    private function ensureTestUserExists(): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'test@user.com']);

        if (!$user) {
            $user = new User();
            $user->setUsername('Test user');
            $user->setEmail('test@user.com');
            $user->setPassword('password');
            $user->setRoles([RoleEnum::USER]);
            $this->em->persist($user);
            $this->em->flush();
        }
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
}