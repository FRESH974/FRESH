<?php

namespace App\Tests\Controller;

use App\Entity\FreshUser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserPasswordHasherInterface $userPasswordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $this->userPasswordHasher = $container->get('security.user_password_hasher');
    }

    public function testRegistrationValidData(): void
    {
        // Test registration with valid data
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('ENTRONS !', [
            'registration_form[email]' => 'newuser@test.com',
            'registration_form[firstname]' => 'New',
            'registration_form[name]' => 'User',
            'registration_form[plainPassword]' => 'newpassword',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertResponseStatusCodeSame(200);
        self::assertRouteSame('app_main');
    }

    public function testRegistrationInvalidData(): void
    {
        // Test registration with invalid data (e.g., missing fields)
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('ENTRONS !', [
            'registration_form[email]' => 'invalidemail',
            'registration_form[firstname]' => '',
            'registration_form[name]' => '',
            'registration_form[plainPassword]' => 'short',
            'registration_form[agreeTerms]' => false
        ]);

        self::assertRouteSame('app_register');
    }

    public function testRegistrationExistingEmail(): void
    {
        // Test registration with an existing email address
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $existingUser = new FreshUser();
        $existingUser->setEmail('existinguser@test.com');
        $existingUser->setFirstName('Existing');
        $existingUser->setName('User');
        $existingUser->setPassword($this->userPasswordHasher->hashPassword($existingUser, 'password123'));

        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('ENTRONS !', [
            'registration_form[email]' => 'existinguser@test.com',
            'registration_form[firstname]' => 'New',
            'registration_form[name]' => 'User',
            'registration_form[plainPassword]' => 'newpassword',
            'registration_form[agreeTerms]' => true,
        ]);

        self::assertRouteSame('app_register');
    }
}
