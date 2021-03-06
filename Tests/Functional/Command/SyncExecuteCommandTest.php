<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Command;

use ONGR\ConnectionsBundle\Command\SyncExecuteCommand;
use ONGR\ConnectionsBundle\Tests\Functional\AbstractESDoctrineTestCase;
use ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional test for ongr:sync:execute command.
 */
class SyncExecuteCommandTest extends AbstractESDoctrineTestCase
{
    /**
     * Check if a document is saved as expected after collecting data from providers.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();
        $this->importData('SyncCommandsTest/ProductsInitialDummyData.sql');
        $this->importData('SyncCommandsTest/SyncStorageWithDummyData.sql');
        $this->importData('SyncCommandsTest/UpdateProductsData.sql');

        $manager = $this->getManager();
        $repository = $manager->getRepository('AcmeTestBundle:Product');

        $application = new Application($kernel);
        $application->add(new SyncExecuteCommand());
        $command = $application->find('ongr:sync:execute');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $search = $repository->createSearch();

        // Temporary workaround for ESB issue #34 (https://github.com/ongr-io/ElasticsearchBundle/issues/34).
        usleep(50000);

        $actualDocuments = iterator_to_array($repository->execute($search));

        $expectedDocuments = $this->getTestingData();

        sort($expectedDocuments);
        sort($actualDocuments);

        $this->assertEquals($expectedDocuments, $actualDocuments);
    }

    /**
     * Builds Product object from array.
     *
     * @param array $products
     *
     * @return Product[]
     */
    private function buildProducts(array $products)
    {
        $productDocuments = [];
        foreach ($products as $product) {
            $repository = $this->getManager()->getRepository('AcmeTestBundle:Product');

            /** @var Product $productDocument */
            $productDocument = $repository->createDocument();
            $productDocument->__setInitialized(true);
            $productDocument->setId($product['id']);
            $productDocument->setTitle($product['title']);
            $productDocument->setPrice($product['price']);
            $productDocument->setDescription($product['description']);
            $productDocument->setLocation($product['location']);
            $productDocument->setScore($product['score']);
            $productDocuments[] = $productDocument;
        }

        return $productDocuments;
    }

    /**
     * Generates testing data.
     *
     * @return \ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document\Product[]
     */
    private function getTestingData()
    {
        $productsData = [
            [
                'id' => '1',
                'title' => 'test product title 1',
                'description' => 'test product description 1',
                'price' => '0.1',
                'location' => null,
                'score' => 1.0,
            ],
            [
                'id' => '3',
                'title' => 'test_prod3',
                'description' => 'test_desc3',
                'price' => '0.3',
                'location' => null,
                'score' => 1.0,
            ],
            [
                'id' => '4',
                'title' => 'test_prod4',
                'description' => 'test_desc4',
                'price' => '0.4',
                'location' => null,
                'score' => 1.0,
            ],
            [
                'id' => '5',
                'title' => 'test_prod5',
                'description' => 'test_desc5',
                'price' => '0.5',
                'location' => null,
                'score' => 1.0,
            ],
            [
                'id' => '6',
                'title' => 'test_prod6',
                'description' => 'test_desc6',
                'price' => '0.6',
                'location' => null,
                'score' => 1.0,
            ],
        ];

        return $this->buildProducts($productsData);
    }
}
