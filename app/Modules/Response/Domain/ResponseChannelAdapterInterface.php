<?php

declare(strict_types=1);

namespace Modules\Response\Domain;

interface ResponseChannelAdapterInterface
{
    public function supports(string $channel): bool;

    /**
     * @return array{external_response_id:string, provider_response:array}
     */
    public function send(string $recipientExternalId, string $body): array;
}
