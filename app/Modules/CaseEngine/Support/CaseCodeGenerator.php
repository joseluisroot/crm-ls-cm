<?php

namespace Modules\CaseEngine\Support;

use Modules\Cases\Models\CaseModel;

class CaseCodeGenerator
{
    public function generate(): string
    {
        $year = date('Y');

        $count = (new CaseModel())
            ->like('public_code', "CIAC-CS-{$year}", 'after')
            ->countAllResults();

        $next = $count + 1;

        return 'CIAC-CS-' . $year . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}