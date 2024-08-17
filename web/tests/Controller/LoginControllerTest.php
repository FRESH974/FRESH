<?php

namespace App\Tests\Controller;

use App\Entity\FreshUser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(FreshUser::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $userPasswordHasher = $container->get('security.user_password_hasher');

        $freshUser = new FreshUser();
        $freshUser->setEmail("test@test.com");
        $freshUser->setFirstName("Test");
        $freshUser->setName("Test");
        $freshUser->setPassword($userPasswordHasher->hashPassword($freshUser, 'hello123'));

        $em->persist($freshUser);
        $em->flush();
    }

    public function testLoginInvalidCredentials(): void
    {
        // Test login with invalid email
        $this->client->request('GET', '/login');
        $this->client->submitForm('ENTRONS !', [
            'email' => 'doesNotExist@example.com',
            'password' => 'password',
        ]);

        // Expect a redirect (302) for invalid credentials
        self::assertResponseStatusCodeSame(302);

        // Follow redirect and ensure we end up back on the login page
        $this->client->followRedirect();
        self::assertRouteSame('app_login'); // Assurez-vous que le nom de la route correspond à la route de connexion
    }

    public function testLoginInvalidPassword(): void
    {
        // Test login with invalid password
        $this->client->request('GET', '/login');
        $this->client->submitForm('ENTRONS !', [
            'email' => 'test@test.com',
            'password' => 'wrong-password',
        ]);

        // Expect a redirect (302) for invalid credentials
        self::assertResponseStatusCodeSame(302);

        // Follow redirect and ensure we end up back on the login page
        $this->client->followRedirect();
        self::assertRouteSame('app_login'); // Assurez-vous que le nom de la route correspond à la route de connexion
    }

    public function testLoginValidCredentials(): void
    {
        // Test login with valid credentials
        $this->client->request('GET', '/login');
        $this->client->submitForm('ENTRONS !', [
            'email' => 'test@test.com',
            'password' => 'hello123',
        ]);

        // Expect a successful response (302) if credentials are correct
        self::assertResponseStatusCodeSame(302);

        // You might want to test if the user is redirected to the homepage
        $this->client->followRedirect();
        self::assertRouteSame('app_main');
    }
}