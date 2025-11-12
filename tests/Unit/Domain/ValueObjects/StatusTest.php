<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\TaskManagement\ValueObjects\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /** @test */
    public function it_can_check_valid_transitions()
    {
        $pending = Status::PENDING;
        $inProgress = Status::IN_PROGRESS;

        $this->assertTrue($pending->canTransitionTo($inProgress));
        $this->assertTrue($pending->canTransitionTo(Status::BLOCKED));
        $this->assertFalse($pending->canTransitionTo(Status::REVIEW));
    }

    /** @test */
    public function completed_status_can_be_reopened()
    {
        $completed = Status::COMPLETED;

        // Can reopen to in_progress
        $this->assertTrue($completed->canTransitionTo(Status::IN_PROGRESS));

        // Cannot transition to other statuses
        $this->assertFalse($completed->canTransitionTo(Status::PENDING));
        $this->assertFalse($completed->canTransitionTo(Status::REVIEW));
        $this->assertFalse($completed->canTransitionTo(Status::BLOCKED));
    }

    /** @test */
    public function it_returns_available_transitions()
    {
        $pending = Status::PENDING;
        $availableTransitions = $pending->availableTransitions();

        $this->assertContains(Status::IN_PROGRESS, $availableTransitions);
        $this->assertContains(Status::BLOCKED, $availableTransitions);
        $this->assertNotContains(Status::COMPLETED, $availableTransitions);
    }

    /** @test */
    public function it_returns_label()
    {
        $this->assertEquals('Pendiente', Status::PENDING->label());
        $this->assertEquals('En Progreso', Status::IN_PROGRESS->label());
        $this->assertEquals('Completada', Status::COMPLETED->label());
    }

    /** @test */
    public function it_returns_color()
    {
        $this->assertEquals('gray', Status::PENDING->color());
        $this->assertEquals('blue', Status::IN_PROGRESS->color());
        $this->assertEquals('green', Status::COMPLETED->color());
        $this->assertEquals('red', Status::BLOCKED->color());
    }
}
