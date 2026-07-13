<?php

declare(strict_types=1);

namespace Modules\Response\Application;

use CodeIgniter\Database\BaseConnection;
use InvalidArgumentException;
use Modules\Operations\Application\CitizenOperationsService;
use Modules\Response\Domain\ResponseChannelAdapterInterface;
use RuntimeException;
use Throwable;

final class ResponseDispatcher
{
    /** @param ResponseChannelAdapterInterface[] $adapters */
    public function __construct(
        private readonly BaseConnection $db,
        private readonly ResponseContextResolver $contextResolver,
        private readonly ResponseDraftService $drafts,
        private readonly CitizenOperationsService $operations,
        private readonly array $adapters,
    ) {
    }

    public function capability(int $workItemId): array
    {
        return $this->contextResolver->capability($workItemId);
    }

    public function dispatch(int $workItemId, ?int $userId, string $body): array
    {
        $body = trim($body);
        if ($body === '') throw new InvalidArgumentException('La respuesta no puede estar vacía.');
        if (mb_strlen($body) > 5000) throw new InvalidArgumentException('La respuesta supera 5,000 caracteres.');

        $context = $this->contextResolver->resolve($workItemId);
        $item = $context['item'];
        $channel = (string) $context['channel'];
        $recipient = (string) $context['recipient_external_id'];

        $this->drafts->save($workItemId, $userId, $channel, $body);
        $draft = $this->drafts->findForWorkItem($workItemId);
        $responseId = $this->insertAttempt($workItemId, $draft['id'] ?? null, $userId, $channel, $recipient, $body, $item);

        try {
            $result = $this->adapter($channel)->send($recipient, $body);
            $now = date('Y-m-d H:i:s');
            $this->db->table('citizen_responses')->where('id', $responseId)->update([
                'status' => 'SENT',
                'external_response_id' => $result['external_response_id'],
                'provider_response_json' => json_encode($result['provider_response'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'sent_at' => $now,
                'updated_at' => $now,
            ]);
            if ($draft) {
                $this->db->table('response_drafts')->where('id', $draft['id'])->update(['status' => 'SENT', 'updated_at' => $now]);
            }
            if (empty($item['first_response_at'])) {
                $this->operations->markResponded($workItemId);
            }
            return ['id' => $responseId, 'status' => 'SENT', 'channel' => $channel, 'external_response_id' => $result['external_response_id']];
        } catch (Throwable $e) {
            $this->db->table('citizen_responses')->where('id', $responseId)->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            throw new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    private function adapter(string $channel): ResponseChannelAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($channel)) return $adapter;
        }
        throw new RuntimeException('No existe adaptador de respuesta para ' . $channel . '.');
    }

    private function insertAttempt(int $workItemId, ?int $draftId, ?int $userId, string $channel, string $recipient, string $body, array $item): int
    {
        $created = strtotime((string) ($item['created_at'] ?? '')) ?: time();
        $now = date('Y-m-d H:i:s');
        $this->db->table('citizen_responses')->insert([
            'work_item_id' => $workItemId,
            'draft_id' => $draftId,
            'user_id' => $userId,
            'channel' => $channel,
            'recipient_external_id' => $recipient,
            'body' => $body,
            'status' => 'PENDING',
            'first_response_ms' => empty($item['first_response_at']) ? max(0, (time() - $created) * 1000) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return (int) $this->db->insertID();
    }
}
