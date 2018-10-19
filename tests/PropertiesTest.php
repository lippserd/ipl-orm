<?php

namespace ipl\Tests\Orm;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPropertyReturnsNullIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertNull($subject->getProperty('foo'));
    }

    public function testGetPropertyReturnsCorrectValueIfSet()
    {
        $subject = (new ClassUsingProperties())
            ->setProperty('foo', 'bar');

        $this->assertSame('bar', $subject->getProperty('foo'));
    }

    public function testGetPropertiesReturnsEmptyArrayIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertSame([], $subject->getProperties());
    }

    public function testGetPropertiesReturnsCorrectAssociativeArrayIfSet()
    {
        $subject = (new ClassUsingProperties())
            ->setProperties(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $subject->getProperties());
    }

    public function testIssetReturnsFalseForArrayAccessIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertFalse(isset($subject['foo']));
    }

    public function testIssetReturnsFalseForPropertyAccessIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertFalse(isset($subject->foo));
    }

    public function testIssetReturnsTrueForArrayAccessIfSet()
    {
        $subject = (new ClassUsingProperties())
            ->setProperty('foo', 'bar');

        $this->assertTrue(isset($subject['foo']));
    }

    public function testIssetReturnsTrueForPropertyAccessIfSet()
    {
        $subject = (new ClassUsingProperties())
            ->setProperty('foo', 'bar');

        $this->assertTrue(isset($subject->foo));
    }

    public function testGetPropertyReturnsNullForArrayAccessIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertNull($subject['foo']);
    }

    public function testGetPropertyReturnsNullForPropertyAccessIfUnset()
    {
        $subject = new ClassUsingProperties();

        $this->assertNull($subject->foo);
    }

    public function testGetPropertyReturnsCorrectValueForArrayAccessIfSet()
    {
        $subject = new ClassUsingProperties();
        $subject['foo'] = 'bar';

        $this->assertSame('bar', $subject['foo']);
    }

    public function testGetPropertyReturnsCorrectValueForPropertyAccessIfSet()
    {
        $subject = new ClassUsingProperties();
        $subject->foo = 'bar';

        $this->assertSame('bar', $subject->foo);
    }

    public function testUnsetForArrayAccess()
    {
        $subject = new ClassUsingProperties();
        $subject['foo'] = 'bar';

        $this->assertSame('bar', $subject['foo']);

        unset($subject['foo']);

        $this->assertNull($subject['foo']);
    }

    public function testUnsetForPropertyAccess()
    {
        $subject = new ClassUsingProperties();
        $subject->foo = 'bar';

        $this->assertSame('bar', $subject->foo);

        unset($subject->foo);

        $this->assertNull($subject->foo);
    }

    public function testGetMutatorGetsCalled()
    {
        $subject = new ClassUsingProperties();

        $this->assertSame('foobar', $subject->foobar);
    }

    public function testSetMutatorGetsCalled()
    {
        $subject = new ClassUsingProperties();
        $subject->special = 'foobar';

        $this->assertSame('FOOBAR', $subject->special);
    }
}
