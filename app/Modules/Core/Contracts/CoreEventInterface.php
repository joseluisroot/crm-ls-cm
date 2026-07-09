<?php

namespace Modules\Core\Contracts;

interface CoreEventInterface
{
    public function name(): string;

    public function payload(): array;
}