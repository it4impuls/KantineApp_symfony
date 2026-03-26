<?php

namespace Kantine\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class OrderTest extends WebTestCase
{

    protected static function createKernel(array $options = []): KernelInterface
    {
        static::$class ??= static::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new static::$class($env, $debug, 'kantine');
    }

    public function loadsKantinePage(): void
    {
        $client = static::createClient();
        // $testUser = new InMemoryUser('admin', 'password', ['ROLE_ADMIN', 'ROLE_USER']);
        // $test = $client->loginUser($testUser, 'main');
        // $usr=$this->get();
        $crawler = $client->request('GET', '/');
        

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.menus'));
    }

    public function postValidOrder(): void
    {
        $client = static::createClient();
        // $testUser = new InMemoryUser('admin', 'password', ['ROLE_ADMIN', 'ROLE_USER']);
        // $test = $client->loginUser($testUser, 'main');
        // $usr=$this->get();
        $crawler = $client->request('GET', '/');
        

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.menus'));
    }
}
