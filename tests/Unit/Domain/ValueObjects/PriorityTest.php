<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\TaskManagement\ValueObjects\Priority;
use PHPUnit\Framework\TestCase;

class PriorityTest extends TestCase
{
    /** @test */
    public function it_returns_correct_score()
    {
        $this->assertEquals(1, Priority::LOW->score());
        $this->assertEquals(2, Priority::MEDIUM->score());
        $this->assertEquals(3, Priority::HIGH->score());
        $this->assertEquals(4, Priority::CRITICAL->score());
    }

    /** @test */
    public function it_can_compare_priorities()
    {
        $low = Priority::LOW;
        $medium = Priority::MEDIUM;
        $high = Priority::HIGH;
        $critical = Priority::CRITICAL;

        $this->assertTrue($critical->isHigherThan($high));
        $this->assertTrue($high->isHigherThan($medium));
        $this->assertTrue($medium->isHigherThan($low));
        $this->assertFalse($low->isHigherThan($medium));
    }

    /** @test */
    public function it_returns_label()
    {
        $this->assertEquals('Low', Priority::LOW->label());
        $this->assertEquals('Medium', Priority::MEDIUM->label());
        $this->assertEquals('High', Priority::HIGH->label());
        $this->assertEquals('Critical', Priority::CRITICAL->label());
    }

    /** @test */
    public function it_returns_color()
    {
        $this->assertEquals('green', Priority::LOW->color());
        $this->assertEquals('yellow', Priority::MEDIUM->color());
        $this->assertEquals('orange', Priority::HIGH->color());
        $this->assertEquals('red', Priority::CRITICAL->color());
    }
}
