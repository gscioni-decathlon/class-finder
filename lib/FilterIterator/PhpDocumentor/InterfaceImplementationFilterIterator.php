<?php

declare(strict_types=1);

namespace Kcs\ClassFinder\FilterIterator\PhpDocumentor;

use FilterIterator;
use Iterator;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\Class_;

use function array_map;
use function in_array;
use function ltrim;

final class InterfaceImplementationFilterIterator extends FilterIterator
{
    /** @var string[] */
    private array $interfaces;

    /**
     * @param Iterator<Element> $iterator
     * @param string[] $interfaces
     * @phpstan-param class-string[] $interfaces
     */
    public function __construct(Iterator $iterator, array $interfaces)
    {
        parent::__construct($iterator);

        $this->interfaces = $interfaces;
    }

    public function accept(): bool
    {
        $reflector = $this->getInnerIterator()->current();
        if (! $reflector instanceof Class_) {
            return false;
        }

        $interfaces = array_map(static fn (string $interface) => ltrim($interface, '\\'), $reflector->getInterfaces());
        foreach ($this->interfaces as $interface) {
            if (in_array($interface, $interfaces, true)) {
                return true;
            }
        }

        return false;
    }
}
