<?php

namespace Shared\Tests;

use Doctrine\ORM\EntityManager;
use Shared\Repository\CostumerRepository;
use Shared\Repository\SonataUserUserRepository;
use Shared\Repository\TagsRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Zeiterfassung\Repository\TimeEntryRepository;

class TagsAdminTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManager $entityManager;
    protected static function createKernel(array $options = []): KernelInterface
    {
        static::$class ??= static::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new static::$class($env, $debug, 'kantine');
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->entityManager->beginTransaction();
        parent::setUp();
    }

    public function tearDown(): void
    {
        if( $this->entityManager->getConnection()->getTransactionNestingLevel()>0)
            $this->entityManager->rollback();
        // else
        //     var_dump("test");
        parent::tearDown();
    }

    /** I have no idea why $this->client->loginUser($adminUser) is not enough, but we have to manually submit the form from /login */
    private function authenticate(): void
    {
        $userRepository = $this->getContainer()->get(SonataUserUserRepository::class);
        $adminUser = $userRepository->findOneByUsername('admin');
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/login', );
        $this->assertResponseIsSuccessful('Could not get login page');
        $this->client->submitForm('login', [
            "_username"=>	"admin",
            "_password"=>	"admin"
        ]);
    }

    public function testAdminList(): void
    {
        $this->authenticate();
        $crawler = $this->client->request('GET', '/admin/shared/tags/list');
        $this->assertResponseIsSuccessful('Could not load Costumer list page');
        $this->assertSelectorExists('.sonata-ba-list', 'Site does not have sonata list');
        
    }

    public function testAdminEdit(): void
    {
        $this->authenticate();
        $tagsRepository = $this->getContainer()->get(TagsRepository::class);
        $tag = null;
        foreach ($tagsRepository->findBy([]) as $key => $value) {
            
        }
        $crawler = $this->client->request('GET', '/admin/shared/tags/list');
        $this->assertResponseIsSuccessful('Could not load Costumer list page');
        $this->assertSelectorExists('.sonata-ba-list', 'Site does not have sonata list');
        
    }
}
