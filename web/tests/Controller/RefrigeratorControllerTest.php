<?php

namespace App\Tests\Controller;

use App\Entity\FreshUser;
use App\Entity\Refrigerator;
use App\Entity\Food;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RefrigeratorControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $container = $this->client->getContainer();
        $this->passwordHasher = $container->get('security.user_password_hasher');
    }

    public function testRedirectToLoginWhenNotAuthenticated()
    {
        $this->client->request('GET', '/refrigerator/want/1');
        $this->assertResponseRedirects('/login');
    }

    public function testIndex()
    {
        $user = $this->createUser();
        $this->createRefrigerator($user);
        $this->client->loginUser($user);

        $this->client->request('GET', '/refrigerator/want/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAddFood()
    {
        $user = $this->createUser();
        $refrigerator = $this->createRefrigerator($user);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/refrigerator/1/food/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form();
        $form['food_form[name]'] = 'Pomme';
        $form['food_form[quantity]'] = 5;
        $form['food_form[expireDate]'] = '2023-12-31';

        $this->client->submit($form);
        $entries = $this->entityManager->getRepository(Food::class)->findBy([
            'refrigerator' => $refrigerator,
            'name' => 'Pomme',
            'quantity' => 5
        ]);

        $this->assertTrue(count($entries) > 0);
        $this->assertNotEmpty($entries);
    }

    public function testModifyFood()
    {
        $user = $this->createUser();
        $refrigerator = $this->createRefrigerator($user);
        $food = $this->createFood($refrigerator);
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/refrigerator/1/food/modify/'.$food->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Modifier')->form();
        $form['food_form[name]'] = 'Banane';
        $form['food_form[quantity]'] = 3;
        $form['food_form[expireDate]'] = '2023-12-31';

        $this->client->submit($form);
        $this->assertResponseRedirects('/refrigerator/want/1');
    }

    public function testRemoveFood()
    {
        $user = $this->createUser();
        $refrigerator = $this->createRefrigerator($user);
        $food = $this->createFood($refrigerator);
        $this->client->loginUser($user);

        $this->client->request('POST', '/refrigerator/1/food/remove/'.$food->getId(), [
            '_remove_'.$food->getId().'_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('_remove_food_refrigerator_token_value')->getValue()
        ]);

        $this->assertResponseRedirects('/refrigerator/want/1');
    }

    public function testAddRefrigerator()
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/refrigerator/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form();
        $form['refrigerator_form[name]'] = 'Mon nouveau frigo';

        $this->client->submit($form);
        $this->assertResponseRedirects('/refrigerator/want/1');
    }

    public function testDeleteRefrigerator()
    {
        $user = $this->createUser();
        $refrigerator = $this->createRefrigerator($user);
        $this->client->loginUser($user);

        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('manual-delete')->getValue();
        $this->client->request('GET', '/refrigerator/1/delete?token='.$token);

        $this->assertResponseRedirects('/');
    }

    private function createUser(): FreshUser
    {
        $userRepository = $this->entityManager->getRepository(FreshUser::class);
        $existingUser = $userRepository->findOneBy(['email' => 'testuser@example.com']);

        if ($existingUser) {
            return $existingUser;
        }
        $user = new FreshUser();
        $user->setEmail("testuser@example.com");
        $user->setFirstName("Test");
        $user->setName("User");
        $user->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    private function createRefrigerator(FreshUser $user): Refrigerator
    {
        $refrigerator = new Refrigerator();
        $refrigerator->setName('Test Refrigerator');
        $refrigerator->setOwner($user);
        $this->entityManager->persist($refrigerator);
        $this->entityManager->flush();
        return $refrigerator;
    }

    private function createFood(Refrigerator $refrigerator): Food
    {
        $food = new Food();
        $food->setName('Test Food');
        $food->setQuantity(1);
        $food->setExpireDate(new \DateTime('+1 week'));
        $food->setRefrigerator($refrigerator);
        $this->entityManager->persist($food);
        $this->entityManager->flush();
        return $food;
    }
}