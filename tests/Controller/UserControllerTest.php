<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Enum\RoleEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->em->getRepository(User::class);
        // Nettoyage en amont pour garantir un état propre
        $this->cleanTestUsers();
    }

    protected function tearDown(): void
    {
        $this->cleanTestUsers();
        parent::tearDown();
    }

    private function cleanTestUsers()
    {
        // Supprime tous les users de test potentiels pour éviter les doublons
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.username LIKE :prefix OR u.username = :admin OR u.username = :edit OR u.username = :userrole')
            ->setParameter('prefix', 'testuser_%')
            ->setParameter('admin', 'admin')
            ->setParameter('edit', 'test_edit')
            ->setParameter('userrole', 'test_userrole')
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $this->em->remove($user);
        }
        $this->em->flush();
    }

    public function testAccessDeniedForNonAdminOnList()
    {
        $this->client->request('GET', '/users');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessDeniedForUserOnList()
    {
        $user = new User();
        $user->setUsername('test_userrole');
        $user->setEmail('test_userrole@example.com');
        $user->setPassword('motdepasse');
        $user->setRoles([RoleEnum::USER]);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/users');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Vous n\'avez pas le droit d\'accéder à cette page.');
    }

    public function testAccessGrantedForAdminOnList()
    {
        $this->loginAsAdmin();
        $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testAccessDeniedForNonAdminOnCreate()
    {
        $this->client->request('GET', '/users/create');
        $this->assertResponseRedirects('/login');
    }

    public function testAccessDeniedForUserOnCreate()
    {
        $user = new User();
        $user->setUsername('test_userrole');
        $user->setEmail('test_userrole@example.com');
        $user->setPassword('motdepasse');
        $user->setRoles([RoleEnum::USER]);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/users/create');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Vous n\'avez pas le droit d\'accéder à cette page.');
    }

    public function testCreateUser()
    {
        $this->loginAsAdmin();

        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'testuser_' . uniqid();
        $form['user[email]'] = 'test' . uniqid() . '@example.com';
        $form['user[password][first]'] = 'motdepasse';
        $form['user[password][second]'] = 'motdepasse';
        $form['user[roles]'] = '0';
        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été ajouté.");
    }

    public function testAccessDeniedForNonAdminOnEdit()
    {
        // Créer un user de test pour éditer
        $user = new User();
        $user->setUsername('test_edit');
        $user->setEmail('test_edit@example.com');
        $user->setPassword('motdepasse');
        $user->setRoles([RoleEnum::USER]);
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertResponseRedirects('/login');
    }

    public function testEditUser()
    {
        $this->loginAsAdmin();

        // Créer un user de test à éditer
        $user = new User();
        $user->setUsername('test_edit');
        $user->setEmail('test_edit@example.com');
        $user->setPassword('motdepasse');
        $user->setRoles([RoleEnum::USER]);
        $this->em->persist($user);
        $this->em->flush();

        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');
        $this->assertResponseIsSuccessful();

        $button = $crawler->selectButton('Sauvegarder');
        $this->assertGreaterThan(0, $button->count(), 'Aucun bouton submit trouvé');
        $form = $button->form();
        $form['user[username]'] = $user->getUsername();
        $form['user[email]'] = 'modif_' . uniqid() . '@example.com';
        $form['user[password][first]'] = 'modifmdp';
        $form['user[password][second]'] = 'modifmdp';
        $form['user[roles]'] = '0';
        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "L'utilisateur a bien été modifié");
    }

    /**
     * Helper pour connecter un admin
     */
    private function loginAsAdmin()
    {
        // Créer un admin propre (supprimé à chaque setUp)
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword('admin');
        $admin->setRoles([RoleEnum::ADMIN]);
        $this->em->persist($admin);
        $this->em->flush();

        $this->client->loginUser($admin);
    }
}