<?php

declare(strict_types=1);

namespace Modules\Publication\Application;

final class CommentThreadService
{
    public function build(array $comments): array
    {
        $nodes = [];

        foreach ($comments as $comment) {
            $id = (int) ($comment['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $nodes[$id] = [
                ...$comment,
                'depth' => 0,
                'children' => [],
                'reply_count' => 0,
                'descendant_count' => 0,
                'is_orphan' => false,
            ];
        }

        $roots = [];

        foreach (array_keys($nodes) as $id) {
            $parentId = (int) ($nodes[$id]['parent_comment_id'] ?? 0);

            if ($parentId > 0 && isset($nodes[$parentId]) && $parentId !== $id) {
                $nodes[$parentId]['children'][] = &$nodes[$id];
                continue;
            }

            if ($parentId > 0 || ! empty($nodes[$id]['external_parent_id'])) {
                $nodes[$id]['is_orphan'] = true;
            }

            $roots[] = &$nodes[$id];
        }

        $maxDepth = 0;
        $replies = 0;
        $orphans = 0;

        foreach ($roots as &$root) {
            $this->decorate($root, 0, $maxDepth, $replies, $orphans);
        }
        unset($root);

        usort($roots, static fn (array $left, array $right): int =>
            strcmp((string) ($right['commented_at'] ?? ''), (string) ($left['commented_at'] ?? ''))
        );

        return [
            'threads' => $roots,
            'metrics' => [
                'root_comments' => count($roots),
                'replies' => $replies,
                'max_depth' => $maxDepth,
                'orphan_comments' => $orphans,
                'total_comments' => count($nodes),
            ],
        ];
    }

    private function decorate(array &$node, int $depth, int &$maxDepth, int &$replies, int &$orphans): int
    {
        $node['depth'] = $depth;
        $maxDepth = max($maxDepth, $depth);

        if ($depth > 0) {
            $replies++;
        }

        if ($node['is_orphan']) {
            $orphans++;
        }

        usort($node['children'], static fn (array $left, array $right): int =>
            strcmp((string) ($left['commented_at'] ?? ''), (string) ($right['commented_at'] ?? ''))
        );

        $descendants = 0;
        foreach ($node['children'] as &$child) {
            $descendants += 1 + $this->decorate($child, $depth + 1, $maxDepth, $replies, $orphans);
        }
        unset($child);

        $node['reply_count'] = count($node['children']);
        $node['descendant_count'] = $descendants;

        return $descendants;
    }
}
