<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTitle()
    {
        $task = new Task();
        $task->setTitle("Titre test");
        $this->assertEquals('Titre test', $task->getTitle());
    }

        public function testContent()
    {
        $task = new Task();
        $task->setContent('Contenu test');
        $this->assertEquals('Contenu test', $task->getContent());
    }

    public function testCreatedAt()
    {
        $task = new Task();
        $date = new \DateTimeImmutable('2022-01-01 12:00:00');
        $task->setCreatedAt($date);
        $this->assertEquals($date, $task->getCreatedAt());
    }

    public function testTaskIsNotDoneByDefault()
    {
        $task = new Task();
        $this->assertFalse($task->isDone());
    }

    public function testToggleTaskToDone()
    {
        $task = new Task();
        $task->toggle(true);
        $this->assertTrue($task->isDone());
    }

    public function testToggleTaskToUndone()
    {
        $task = new Task();
        $task->toggle(true);
        $this->assertTrue($task->isDone());
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testUser()
    {
        $task = new Task();
        $user = $this->createMock(User::class);
        $task->setUser($user);
        $this->assertSame($user, $task->getUser());
    }
}