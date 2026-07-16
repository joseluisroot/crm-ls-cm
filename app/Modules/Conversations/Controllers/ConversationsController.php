<?php

namespace Modules\Conversations\Controllers;

use App\Controllers\BaseController;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;

class ConversationsController extends BaseController
{
    public function index()
    {
        $query = trim((string) $this->request->getGet('q'));
        $channel = trim((string) $this->request->getGet('channel'));
        $status = trim((string) $this->request->getGet('status'));
        $perPage = (int) $this->request->getGet('per_page');
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $conversationModel = new ConversationModel();
        $conversationModel
            ->select('conversations.*, citizens.name as citizen_name, citizens.community, citizens.municipality')
            ->join('citizens', 'citizens.id = conversations.citizen_id');

        if ($query !== '') {
            $conversationModel->groupStart()
                ->like('citizens.name', $query)
                ->orLike('citizens.municipality', $query)
                ->orLike('citizens.community', $query)
                ->orLike('conversations.channel', $query)
                ->orLike('conversations.status', $query)
                ->groupEnd();
        }

        if ($channel !== '') {
            $conversationModel->where('conversations.channel', $channel);
        }

        if ($status !== '') {
            $conversationModel->where('conversations.status', $status);
        }

        $total = $conversationModel->countAllResults(false);
        $pageCount = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pageCount);
        $conversations = $conversationModel
            ->orderBy('conversations.last_message_at', 'DESC')
            ->orderBy('conversations.created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        return view('Modules\Conversations\Views\index', [
            'title' => 'Conversaciones',
            'conversations' => $conversations,
            'filters' => [
                'q' => $query,
                'channel' => $channel,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'total' => $total,
            'page' => $page,
            'pageCount' => $pageCount,
            'from' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function show($id)
    {
        $conversationModel = new ConversationModel();

        $conversation = $conversationModel
            ->select('conversations.*, citizens.name as citizen_name')
            ->join('citizens', 'citizens.id = conversations.citizen_id')
            ->where('conversations.id', $id)
            ->first();

        if (! $conversation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Conversación no encontrada');
        }

        return view('Modules\Conversations\Views\show', [
            'title' => 'Detalle de conversación',
            'conversation' => $conversation,
            'messages' => (new MessageModel())
                ->where('conversation_id', $id)
                ->orderBy('created_at', 'ASC')
                ->findAll(),
        ]);
    }
}
