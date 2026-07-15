<?php

declare(strict_types=1);

namespace Modules\Operations\Controllers;

use App\Controllers\BaseController;
use Modules\Operations\Application\MyWorkQueryService;

final class MyWorkController extends BaseController
{
    public function index()
    {
        $userId = (int) session()->get('admin_user_id');
        $data = (new MyWorkQueryService(db_connect()))->dashboard($userId);

        return view('Modules\Operations\Views\my_work', $data + [
            'title' => 'Mi trabajo',
            'userName' => (string) (session()->get('admin_user_name') ?: 'Operador'),
        ]);
    }
}
