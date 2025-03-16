<?php

namespace App\Form;

use App\Enum\RoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
            ])
            ->add('email', EmailType::class, ['label' => 'Adresse email'])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => RoleEnum::USER,
                    'Administrateur' => RoleEnum::ADMIN,
                ],
                'choice_label' => fn ($choice) => match($choice) {
                    RoleEnum::USER => '👤 Utilisateur',
                    RoleEnum::ADMIN => '🔧 Administrateur',
                },
                'expanded' => false,
                'multiple' => false, 
                'mapped' => false, // On empêche Symfony de faire le mapping automatique
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $user = $event->getData();
                $form = $event->getForm();
            
                if ($form->has('roles')) {
                    $selectedRole = $form->get('roles')->getData(); // Récupère le choix unique
                    if ($selectedRole) {
                        $user->setSingleRole($selectedRole); // Utilise notre setter spécial
                    }
                }
            });         
        ;
    }
}
