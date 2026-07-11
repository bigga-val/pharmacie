<?php

namespace App\Test\Controller;

use App\Entity\Approvisionnement;
use App\Repository\ApprovisionnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApprovisionnementControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ApprovisionnementRepository $repository;
    private string $path = '/approvisionnement/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Approvisionnement::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Approvisionnement index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'approvisionnement[qty]' => 'Testing',
            'approvisionnement[createdAt]' => 'Testing',
            'approvisionnement[createdBy]' => 'Testing',
            'approvisionnement[produit]' => 'Testing',
        ]);

        self::assertResponseRedirects('/approvisionnement/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Approvisionnement();
        $fixture->setQty('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCreatedBy('My Title');
        $fixture->setProduit('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Approvisionnement');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Approvisionnement();
        $fixture->setQty('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCreatedBy('My Title');
        $fixture->setProduit('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'approvisionnement[qty]' => 'Something New',
            'approvisionnement[createdAt]' => 'Something New',
            'approvisionnement[createdBy]' => 'Something New',
            'approvisionnement[produit]' => 'Something New',
        ]);

        self::assertResponseRedirects('/approvisionnement/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getQty());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getCreatedBy());
        self::assertSame('Something New', $fixture[0]->getProduit());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Approvisionnement();
        $fixture->setQty('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setCreatedBy('My Title');
        $fixture->setProduit('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/approvisionnement/');
    }
}
