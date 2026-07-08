<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConversationModel;
use App\Models\MessageModel;
use CodeIgniter\HTTP\ResponseInterface;

class ConversationsController extends BaseController
{
    public function index()
    {
        $conversationModel = new ConversationModel();

        $conversations = $conversationModel
            ->select('conversations.*, citizens.name as citizen_name, citizens.community, citizens.municipality')
            ->join('citizens', 'citizens.id = conversations.citizen_id')
            ->orderBy('last_message_at', 'DESC')
            ->paginate(20);

        $data = [
            'title' => 'Conversaciones',
            'conversations' => $conversations,
            'pager' => $conversationModel->pager,
        ];

        return view('admin/conversations/index', $data);
    }

    public function show($id)
    {
        $conversationModel = new ConversationModel();
        $messageModel = new MessageModel();

        $conversation = $conversationModel
            ->select('conversations.*, citizens.name as citizen_name')
            ->join('citizens', 'citizens.id = conversations.citizen_id')
            ->where('conversations.id', $id)
            ->first();

        if (!$conversation) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Conversación no encontrada');
        }

        $data = [
            'title' => 'Detalle de conversación',
            'conversation' => $conversation,
            'messages' => $messageModel
                ->where('conversation_id', $id)
                ->orderBy('created_at', 'ASC')
                ->findAll(),
        ];

        return view('admin/conversations/show', $data);
    }
}
