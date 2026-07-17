<?php

namespace Zeiterfassung\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Shared\Repository\SonataUserUserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Zeiterfassung\Repository\ScannerClientRepository;
use Zeiterfassung\Repository\ScannerLogEntryRepository;

final class ScannerClientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManager $entityManager;
    protected static function createKernel(array $options = []): KernelInterface
    {
        static::$class ??= static::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        return new static::$class($env, $debug, 'zeiterfassung');
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
        parent::tearDown();
    }

     /** I have no idea why $this->client->loginUser($adminUser) is not enough, but we have to manually submit the form from /login */
    private function authenticate(): void
    {
        $userRepository = $this->getContainer()->get(SonataUserUserRepository::class);
        $adminUser = $userRepository->findOneByUsername('admin');
        $this->client->loginUser($adminUser);
        // 

        // $this->loginUser()
        $this->client->request('GET', '/admin/logout', );
        $this->client->request('GET', '/admin/login', );
        // var_dump($this->client->getResponse()->getContent());
        $this->assertResponseIsSuccessful('Could not get login page');
        $this->client->submitForm('submit', [
            "_username"=>	"admin",
            "_password"=>	"admin"
        ]);
        // var_dump($this->client->getResponse());
    }

    private function getToken(): string
    {
        $userRepository = static::getContainer()->get(SonataUserUserRepository::class);
        $testUser = $userRepository->findOneByUsername('admin');
        $this->client->loginUser($testUser, 'main');
        // for some reason the Request fails with invalid credentals if you are not logged in??
        $this->client->request('POST', '/api/login', content: json_encode(["username" => "admin", "password" => "admin"]));
        $content = $this->client->getResponse()->getContent();

        $this->assertResponseIsSuccessful('Could not get JWT Token: '.$content);

        $deserialized =  json_decode($content, true);
        $this->assertTrue(array_key_exists("token", $deserialized), "Response does not have token: ".$content);
        return $deserialized["token"];
    }
    
    public function testScannerPing(): void
    {
        // $this->authenticate();
        $token = $this->getToken();
        $this->client->request('GET', '/api/scanner/test/', server:[
            "HTTP_AUTHORIZATION" => "Bearer ".$token
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testUploadLog(): void
    {
        // $this->authenticate();
        $token = $this->getToken();
        $payload = ['level' => 'INFO', 'message' => 'test'];
        $this->client->request('POST', '/api/scanner/test/log', content:json_encode($payload), server:[
            "HTTP_AUTHORIZATION" => "Bearer ".$token
        ]);
        self::assertResponseIsSuccessful();

        $scannerRepository = $this->getContainer()->get(ScannerClientRepository::class);
        $logRepository = $this->getContainer()->get(ScannerClientRepository::class);
        $scanner = $scannerRepository->findOneBy(['uname' => 'test']);
        $this->assertNotNull($scanner, "scanner not found");
        $log = $logRepository->findBy(array_merge(["scanner"=> $scanner], $payload));
        $this->assertNotNull($log, "log not found");

    }
}
