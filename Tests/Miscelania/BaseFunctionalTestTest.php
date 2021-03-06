<?php

/*
 * This file is part of the BaseBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\BaseBundle\Tests\Miscelania;

use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Symfony\Component\HttpKernel\KernelInterface;

use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Mmoreram\BaseBundle\Tests\BaseKernel;
use Mmoreram\BaseBundle\Tests\Bundle\DependencyInjection\TestStandardMappingBagProvider;
use Mmoreram\BaseBundle\Tests\Bundle\Entity\User;
use Mmoreram\BaseBundle\Tests\Bundle\TestMappingBundle;

/**
 * Class BaseFunctionalTestTest.
 */
class BaseFunctionalTestTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel() : KernelInterface
    {
        return new BaseKernel([
            new DoctrineFixturesBundle(),
            new TestMappingBundle(
                new TestStandardMappingBagProvider()
            ),
        ], [
            'imports' => [
                ['resource' => '@BaseBundle/Resources/config/providers.yml'],
                ['resource' => '@BaseBundle/Resources/test/doctrine.test.yml'],
            ],
        ]);
    }

    /**
     * Load fixtures of these bundles.
     *
     * @return array
     */
    protected static function loadFixturePaths() : array
    {
        return [
            '@TestMappingBundle',
        ];
    }

    /**
     * Test get.
     */
    public function testGet()
    {
        $this->assertInstanceOf(
            'Mmoreram\BaseBundle\Tests\Bundle\TestClass',
            $this->get('test.service')
        );
    }

    /**
     * Test has.
     */
    public function testHas()
    {
        $this->assertTrue($this->has('test.service'));
    }

    /**
     * Test get parameter.
     */
    public function testGetParameter()
    {
        $this->assertEquals(
            '1234',
            $this->getParameter('kernel.secret')
        );
    }

    /**
     * Test get object manager.
     *
     * @dataProvider getEntityNamespace
     */
    public function testGetObjectManager(string $entityNamespace)
    {
        $this->assertInstanceOf(
            'Doctrine\Common\Persistence\ObjectManager',
            $this->getObjectManager('Mmoreram\BaseBundle\Tests\Bundle\Entity\User')
        );

        $this->assertInstanceOf(
            'Doctrine\Common\Persistence\ObjectManager',
            $this->getObjectManager($entityNamespace)
        );
    }

    /**
     * Test get object repository.
     *
     * @dataProvider getEntityNamespace
     */
    public function testGetObjectRepository(string $entityNamespace)
    {
        $this->assertInstanceOf(
            'Doctrine\Common\Persistence\ObjectRepository',
            $this->getObjectRepository('Mmoreram\BaseBundle\Tests\Bundle\Entity\User')
        );

        $this->assertInstanceOf(
            'Doctrine\Common\Persistence\ObjectRepository',
            $this->getObjectRepository($entityNamespace)
        );
    }

    /**
     * Test find.
     *
     * @dataProvider getEntityNamespace
     */
    public function testFind(string $entityNamespace)
    {
        $this->assertEquals(
            1,
            $this->find($entityNamespace, 1)->getId()
        );
    }

    /**
     * Test find one by.
     *
     * @dataProvider getEntityNamespace
     */
    public function testFindOneBy(string $entityNamespace)
    {
        $this->assertEquals(
            1,
            $this->findOneBy($entityNamespace, [
                'name' => 'Joan',
            ])->getId()
        );
    }

    /**
     * Test find all.
     *
     * @dataProvider getEntityNamespace
     */
    public function testFindAll(string $entityNamespace)
    {
        $this->assertCount(
            3,
            $this->findAll($entityNamespace)
        );
    }

    /**
     * Test find by.
     *
     * @dataProvider getEntityNamespace
     */
    public function testFindBy(string $entityNamespace)
    {
        $this->reloadFixtures();

        $user = new User();
        $user->setName('Joan');
        $this->save($user);

        $this->assertCount(
            2,
            $this->findBy($entityNamespace, [
                'name' => 'Joan',
            ])
        );
    }

    /**
     * Test save.
     *
     * @dataProvider getEntityNamespace
     */
    public function testSave(string $entityNamespace)
    {
        $this->reloadFixtures();

        // In fixtures, saved 3 users already
        $user4 = new User();
        $user4->setName('Marc');

        $this->save($user4);
        $this->assertNotNull($user4->getId());

        $user4->setName('India');
        $user5 = new User();
        $user5->setName('Sara');

        $user6 = new User();
        $user6->setName('Yepa');

        $this->save([
            $user4,
            $user5,
            $user6,
        ]);

        $this->assertEquals('India', $this->find($entityNamespace, 4)->getName());
        $this->assertEquals('Sara', $this->find($entityNamespace, 5)->getName());
        $this->assertEquals('Yepa', $this->find($entityNamespace, 6)->getName());
    }

    /**
     * Get entity namespace.
     */
    public function getEntityNamespace()
    {
        return [
            ['Mmoreram\BaseBundle\Tests\Bundle\Entity\User'],
            //['TestMappingBundle:User'],
            ['my_prefix.entity.user.class'],
            ['my_prefix:user'],
        ];
    }
}
