<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /** @test */
    public function it_can_run_basic_test()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_perform_basic_calculations()
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
    }
}