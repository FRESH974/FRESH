<?php

namespace App\Tests\Controller;

use App\Entity\FreshUser;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MainControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $this->passwordHasher = $container->get('security.user_password_hasher');

        // Create a user
        $user = new FreshUser();
        $user->setEmail("testuser@example.com");
        $user->setFirstName("Test");
        $user->setName("User");
        $user->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        $em = $container->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        // Authenticate the user
        $this->client->loginUser($user);
    }

    protected function tearDown(): void
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $em->createQuery('DELETE FROM App\Entity\FreshUser')->execute();
        $em->clear();
        parent::tearDown();
    }

    public function testAccessAccountPage(): void
    {
        $this->client->request('GET', '/account');

        // Expect a successful response (200)
        self::assertResponseIsSuccessful();
    }

    public function testAccessAccountPageWithoutLogin(): void
    {
        // Ensure the client is logged out
        $this->client->request('GET', '/logout');

        $this->client->request('GET', '/account');

        // Assert that the response is a redirect
        self::assertResponseRedirects();

        // Get the redirect URL
        $redirectUrl = $this->client->getResponse()->headers->get('Location');

        // Assert that the redirect URL is the login page
        self::assertStringContainsString('/login', $redirectUrl);
    }

    public function testModifyAccountInformation(): void
    {
        $this->client->request('GET', '/account?modify=true');

        // Check that the modify form is displayed
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $this->client->submitForm('MODIFIER', [
            'fresh_user_form[email]' => 'updateduser@example.com',
            'fresh_user_form[firstname]' => 'Updated',
            'fresh_user_form[name]' => 'User',
            'fresh_user_form[plainPassword]' => 'newpassword',
        ]);

        // Verify that the user information was updated in the database
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $updatedUser = $em->getRepository(FreshUser::class)->findOneBy(['email' => 'updateduser@example.com']);
        self::assertNotNull($updatedUser);
        self::assertEquals('UPDATED', $updatedUser->getFirstname());
        self::assertEquals('USER', $updatedUser->getName());
        self::assertTrue($this->passwordHasher->isPasswordValid($updatedUser, 'newpassword'));
    }

    public function testInvalidFormSubmission(): void
    {
        $this->client->request('GET', '/account?modify=true');

        // Check that the modify form is displayed
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        // Submit invalid data
        $this->client->submitForm('MODIFIER', [
            'fresh_user_form[email]' => 'invalidemail',
            'fresh_user_form[firstname]' => '',
            'fresh_user_form[name]' => '',
            'fresh_user_form[plainPassword]' => 'short',
        ]);

        // Expect a response with validation errors
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.form-error-message', 'This value is not a valid email address.');
        self::assertSelectorTextContains('.form-error-message', 'This value should not be blank.');
        self::assertSelectorTextContains('.form-error-message', 'This value is too short.');
        self::assertSelectorTextContains('.form-error-message', 'Vous devez accepter les conditions d\'utilisation');
    }
}