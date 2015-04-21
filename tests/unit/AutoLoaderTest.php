<?php 

use Mockery as m;

use Packaging\AutoLoader;

class AutoLoaderTest extends PHPUnit_Framework_TestCase
{

    public function testResolveFileNameOneLevel()
    {
        $libDir = $this->fakeLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $fileName = realpath($libDir).'/FakeLib/Loader.php';
        $testClass = 'FakeLib\Loader';

        $this->assertEquals($fileName, $loader->resolveFile($testClass));
    }

    public function testResolveFileNameTwoLevel()
    {
        $libDir = $this->fakeLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $fileName = realpath($libDir).'/FakeLib/Faker/Factory.php';
        $testClass = 'FakeLib\Faker\Factory';

        $this->assertEquals($fileName, $loader->resolveFile($testClass));
    }

    public function testResolveFileNameWithMultilevelAssignedNamespace()
    {
        $libDir = $this->fakeLibDir();
        $lib2Dir = $this->secondLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $loader->addNamespace('SecondLib\Types', $lib2Dir);
        $fileName = realpath($libDir).'/FakeLib/Faker/Factory.php';
        $testClass = 'FakeLib\Faker\Factory';
        $fileName2 = realpath($libDir).'/Types/AbstractType.php';
        $testClass2 = 'SecondLib\Types\AbstractType';

        $this->assertEquals($fileName, $loader->resolveFile($testClass));
        $this->assertEquals($fileName2, $loader->resolveFile($testClass2));
    }

    public function testAutoloadLoadsOneLevel()
    {
        $libDir = $this->fakeLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $fileName = realpath($libDir).'/FakeLib/Loader.php';
        $testClass = 'FakeLib\Loader';

        $loader->autoload($testClass);

        $this->assertTrue(class_exists($testClass));
    }

    public function testAutoloadLoadsTwoLevel()
    {
        $libDir = $this->fakeLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $fileName = realpath($libDir).'/FakeLib/Faker/Factory.php';
        $testClass = 'FakeLib\Faker\Factory';

        $loader->autoload($testClass);

        $this->assertTrue(class_exists($testClass));
    }

    public function testAutoloadLoadsMultilevelAssignedNamespace()
    {
        $libDir = $this->fakeLibDir();
        $lib2Dir = $this->secondLibDir();
        $loader = $this->newLoader();

        $loader->addNamespace('FakeLib', $libDir);
        $loader->addNamespace('SecondLib\Types', $lib2Dir);
        $fileName = realpath($libDir).'/FakeLib/Faker/Factory.php';
        $testClass = 'FakeLib\Faker\Factory';
        $fileName2 = realpath($libDir).'/Types/AbstractType.php';
        $testClass2 = 'SecondLib\Types\AbstractType';

        $loader->autoload($testClass2);
        $this->assertTrue(class_exists($testClass2));
    }

    protected function newLoader()
    {
        return new AutoLoader;
    }

    protected function fakeLibDir()
    {
        return __DIR__.'/../helpers';
    }

    protected function secondLibDir()
    {
        return __DIR__.'/../helpers';
    }

    public function tearDown()
    {
        m::close();
    }

}