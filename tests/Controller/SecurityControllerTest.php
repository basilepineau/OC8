<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }
    
    public function testLoginWithInvalidCredentialsShowsError()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'wrong_user',
            '_password' => 'wrong_pass',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();

        $client->followRedirect();
        $this->assertSelectorTextContains('.alert', 'Identifiants invalides');
    }
}