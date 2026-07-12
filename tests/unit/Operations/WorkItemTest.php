<?php

namespace Tests\Unit\Operations;

use DateTimeImmutable;
use DomainException;
use Modules\Operations\Domain\Entities\WorkItem;
use Modules\Operations\Domain\ValueObjects\WorkItemPriority;
use Modules\Operations\Domain\ValueObjects\WorkItemStatus;
use PHPUnit\Framework\TestCase;

final class WorkItemTest extends TestCase
{
    public function testAssignChangesStatusAndOperator(): void
    {
        $workItem = $this->newWorkItem();
        $workItem->assign(15);

        $data = $workItem->toArray();

        self::assertSame(15, $data['assigned_user_id']);
        self::assertSame(WorkItemStatus::ASSIGNED, $data['status']);
    }

    public function testTerminalWorkItemCannotBeAssigned(): void
    {
        $workItem = $this->newWorkItem();
        $workItem->close(new DateTimeImmutable('2026-07-11 20:00:00'));

        $this->expectException(DomainException::class);
        $workItem->assign(15);
    }

    public function testLinkCaseChangesStatus(): void
    {
        $workItem = $this->newWorkItem();
        $workItem->linkCase(42);

        $data = $workItem->toArray();

        self::assertSame(42, $data['case_id']);
        self::assertSame(WorkItemStatus::CASE_CREATED, $data['status']);
    }

    private function newWorkItem(): WorkItem
    {
        return new WorkItem(
            uuid: '123e4567-e89b-42d3-a456-426614174000',
            originType: 'FACEBOOK_COMMENT',
            originId: 'comment-1',
            channel: 'FACEBOOK',
            title: 'Comentario ciudadano',
            summary: 'Necesitamos atención.',
            status: new WorkItemStatus(WorkItemStatus::NEW),
            priority: new WorkItemPriority(WorkItemPriority::NORMAL),
        );
    }
}
