<?php

namespace ipl\Tests\Orm;

use ipl\Orm;

class StrTest extends \PHPUnit_Framework_TestCase
{
    public function testCamelTransformDoesNothingIfSubjectHasNoDelimiters()
    {
        $str = 'nocase';

        $this->assertSame($str, Orm\Str::camel($str));
    }

    public function testCamelTransformDoesNothingIfSubjectIsAlreadyCamelCase()
    {
        $str = 'camelCase';

        $this->assertSame($str, Orm\Str::camel($str));
    }

    public function testCamelTransformFromSnakeCaseSubject()
    {
        $str = 'snake_case';

        $this->assertSame('snakeCase', Orm\Str::camel($str));
    }

    public function testCamelTransformFromKebabCaseSubject()
    {
        $str = 'kebab-case';

        $this->assertSame('kebabCase', Orm\Str::camel($str));
    }
}
