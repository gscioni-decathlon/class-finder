<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\FilteredComposerIterator;
use PHPUnit\Framework\TestCase;

class FilteredComposerIteratorTest extends TestCase
{
    /**
     * @var ClassLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->classMap = [
                __CLASS__ => __FILE__,
            ];

            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];

            $loader->prefixesPsr0 = [
                'K' => [
                    'Kcs\\ClassFinder\\Fixtures\\Psr0\\' => [
                        __DIR__.'/../..'.'/data/Composer/Psr0',
                    ],
                ],
            ];
        }, null, ClassLoader::class))();

        $this->loader = $loader;
    }

    public function testComposerIteratorShouldWork()
    {
        $iterator = new FilteredComposerIterator($this->loader, null, null);

        self::assertEquals([
            __CLASS__ => new \ReflectionClass(__CLASS__),
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldFilterNotIntersectingPath()
    {
        // NOTE: This test could be interpreted as wrong, but is not:
        // the purpose of the FilteredComposerIterator class is to do some *quick and dirty* filtering
        // not to be precise enough to be used directly. In this case the Psr4/ direct children
        // intersects perfectly with the requested dirs. The upper finder should filter out the
        // non-matching results.

        $iterator = new FilteredComposerIterator($this->loader, null, [  __DIR__.'/../..'.'/data/Composer/Psr4/SubNs' ]);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }
}
