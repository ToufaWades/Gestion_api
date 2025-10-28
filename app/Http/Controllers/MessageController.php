<?php

namespace App\Http\Controllers;

use App\Services\MessageServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MessageController extends Controller
{
    private MessageServiceInterface $messageService;

    public function __construct(MessageServiceInterface $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Generic send endpoint. Accepts JSON { to, message } and uses the bound service.
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $ok = $this->messageService->sendMessage($data['to'], $data['message']);

        return response()->json(['success' => $ok], $ok ? 200 : 500);
    }
}
