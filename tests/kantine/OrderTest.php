<?php

namespace Kantine\Tests;

use DateTime;
use Kantine\Form\OrderDTOType;
use Kantine\Form\OrderFormDTO;
use Kantine\Repository\OrderRepository;
use Shared\Repository\CostumerRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

class OrderTest extends WebTestCase
{

    protected static function createKernel(array $options = []): KernelInterface
    {
        static::$class ??= static::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new static::$class($env, $debug, 'kantine');
    }

    // protected function pre

    public function testLoadsKantinePage(): void
    {
        $client = static::createClient();
        $testUser = new InMemoryUser('admin', 'password', ['ROLE_ADMIN', 'ROLE_USER']);
        $test = $client->loginUser($testUser, 'main');
        $crawler = $client->request('GET', '/');
        

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.menus'));
    }

    public function testPostDuplicateOrder(): void
    {
        $client = static::createClient();
        // $testUser = new InMemoryUser('admin', 'password', ['ROLE_ADMIN', 'ROLE_USER']);
        // $test = $client->loginUser($testUser, 'main');
        // $usr=$this->get();

        
        $newOrder = new OrderDTOType();
        $crawler = $client->request('GET', '/', );
        

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.menus'));
    }

    public function testPostValidOrder(): void
    {
        $client = self::createClient();
        $container = static::getContainer();
        $crawler = $client->request('GET', '/', );

        $costumerRepository = $container->get(CostumerRepository::class);
        $orderRepository = $container->get(OrderRepository::class);
        $newOrder = new OrderFormDTO()
            ->setOrderedItem(rand(10, 100)/10)
            ->setTax(7);
        $costumers = $costumerRepository->getAll();
        $count = 0;
        foreach ($costumers as $costumer) {
            if($orderRepository->findCostumerOrderAtDate($costumer, new DateTime()))
                continue;
            else {
                $crawler = $client->submitForm('order_dto_save', [
                    "order_dto[Costumer]" => $costumer->getId(),
                    "order_dto[ordered_item]" => "4.5",
                    "order_dto[tax]" => "7",
                    // "order_dto[_token]" => "fhr8d5sha3a69tpv24s5"
                ]);
                $this->assertResponseIsSuccessful();
                $count++;
            }    
        }
        $this->assertFalse($count=== 0, 'No valid costumers found. Do all have already ordered?');
            
        // $crawler = $client->request('POST', '/', content: json $newOrder);
        // $this->json_encode($newOrder);
        

        
        // $this->assertCount(1, $crawler->filter('.menus'));
    }
}
