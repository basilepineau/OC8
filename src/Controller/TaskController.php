<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;

class TaskController extends AbstractController
{

    #[Route('/tasks', name: 'task_list')]
    public function listAction(Request $request, EntityManagerInterface $em)
    {
        $isDone = $request->query->getBoolean('done', false); // ?done=true
    
        $tasks = $em->getRepository(Task::class)->findBy([
            'isDone' => $isDone,
        ]);
    
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'showDone' => $isDone,
        ]);
    }    


    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(Request $request, EntityManagerInterface $em)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($this->getUser());

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, EntityManagerInterface $em, Security $security)
    {
        $user = $security->getUser();

        if ($task->getUser() !== $user && !$security->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de modifier cette tâche car elle ne vous appartient pas.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('task_list'));
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task, EntityManagerInterface $em)
    {
        $task->toggle(!$task->isDone());
        $em->flush();

        $this->addFlash('success', sprintf('La %s a bien été marquée comme terminée.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, EntityManagerInterface $em)
    {
        // Si tâche de l'utilisateur "anonyme" et que l'utilisateur actuel n'est pas admin
        if ($task->getUser() && in_array(User::ROLE_ANONYME, $task->getUser()->getRoles(), true) && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Seuls les administrateurs peuvent supprimer une tâche appartenant à un utilisateur anonyme.');
            return $this->redirectToRoute('task_list');
        }

        if ($task->getUser() !== $this->getUser() && !in_array(User::ROLE_ANONYME, $task->getUser()->getRoles(), true)) {
            $this->addFlash('error', 'Vous n\'avez pas le droit de supprimer cette tâche car elle ne vous appartient pas.');
            return $this->redirectToRoute('task_list');
        }
    
        $em->remove($task);
        $em->flush();
    
        $this->addFlash('success', 'La tâche a bien été supprimée.');
    
        return $this->redirectToRoute('task_list');
    }    
}
