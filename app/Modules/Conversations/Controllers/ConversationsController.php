<?php

namespace Modules\Conversations\Controllers;

use App\Controllers\BaseController;
use Modules\Conversations\Models\ConversationModel;
use Modules\Conversations\Models\MessageModel;

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

        return view('Modules\Conversations\Views\index', [
            'title' => 'Conversaciones',
            'conversations' => $conversations,
            'pager' => $conversationModel->pager,
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

        if (!$conversation) {
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
