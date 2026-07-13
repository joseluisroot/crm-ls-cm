<?php

declare(strict_types=1);

namespace Tests\Unit\Publication;

use Modules\Publication\Application\CommentThreadService;
use PHPUnit\Framework\TestCase;

final class CommentThreadServiceTest extends TestCase
{
    public function testBuildsNestedThreadsAndMetrics(): void
    {
        $result = (new CommentThreadService())->build([
            ['id' => 1, 'parent_comment_id' => null, 'external_parent_id' => null, 'commented_at' => '2026-07-13 10:00:00'],
            ['id' => 2, 'parent_comment_id' => 1, 'external_parent_id' => 'ext-1', 'commented_at' => '2026-07-13 10:01:00'],
            ['id' => 3, 'parent_comment_id' => 2, 'external_parent_id' => 'ext-2', 'commented_at' => '2026-07-13 10:02:00'],
        ]);

        self::assertCount(1, $result['threads']);
        self::assertSame(1, $result['metrics']['root_comments']);
        self::assertSame(2, $result['metrics']['replies']);
        self::assertSame(2, $result['metrics']['max_depth']);
        self::assertSame(2, $result['threads'][0]['descendant_count']);
    }

    public function testKeepsOrphanReplyVisibleAsRoot(): void
    {
        $result = (new CommentThreadService())->build([
            ['id' => 8, 'parent_comment_id' => 999, 'external_parent_id' => 'missing', 'commented_at' => '2026-07-13 10:00:00'],
        ]);

        self::assertCount(1, $result['threads']);
        self::assertTrue($result['threads'][0]['is_orphan']);
        self::assertSame(1, $result['metrics']['orphan_comments']);
    }
}
