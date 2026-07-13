<?php

declare(strict_types=1);

namespace Tests\Unit\Publication;

use Modules\Publication\Application\FanPageActorClassifier;
use PHPUnit\Framework\TestCase;

final class FanPageActorClassifierTest extends TestCase
{
    public function testItClassifiesOnlyExactPageIdMatchesAsInstitutional(): void
    {
        $classifier = new FanPageActorClassifier();

        $profile = $classifier->enrich([
            'publication' => ['page_id' => 'page-123'],
            'comments' => [
                ['author_external_id' => 'page-123', 'author_name' => 'Fan Page'],
                ['author_external_id' => 'citizen-9', 'author_name' => 'Ana'],
            ],
            'participants' => [
                ['external_id' => 'page-123', 'name' => 'Fan Page'],
                ['external_id' => 'citizen-9', 'name' => 'Ana'],
            ],
        ]);

        self::assertTrue($profile['comments'][0]['is_page_actor']);
        self::assertSame('INSTITUTION', $profile['comments'][0]['actor_scope']);
        self::assertFalse($profile['comments'][1]['is_page_actor']);
        self::assertSame(1, $profile['actor_metrics']['institutional_comments']);
        self::assertSame(1, $profile['actor_metrics']['institutional_participants']);
    }

    public function testNameSimilarityDoesNotClassifyAnActorAsThePage(): void
    {
        $classifier = new FanPageActorClassifier();

        self::assertFalse($classifier->isPageActor('another-id', 'page-123'));
        self::assertFalse($classifier->isPageActor('', 'page-123'));
        self::assertFalse($classifier->isPageActor('page-123', ''));
    }
}
