<?php

namespace App\Http\Controllers;

use App\Services\SocketService;
use Illuminate\Http\Request;

class EFTController extends Controller
{
    public function sendPaymentRequest(Request $request)
    {
        $amount = $request->input('amount');
        $socketService = SocketService::getInstance();

        if (!$socketService->isConnected()) {
            $socketService->reconnect();
        }

        if (!$socketService->isConnected()) {
            return response()->json(['error' => 'Socket connection failed, please retry'], 500);
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <Esp:Interface xmlns:Esp=\"http://www.mosaicsoftware.com/Postilion/eSocket.POS/\" Version=\"1.0\">
                <Esp:Transaction TerminalId=\"ESADZA01\" Type=\"PURCHASE\" Amount=\"$amount\" Currency=\"USD\" />
            </Esp:Interface>";

        $response = $socketService->sendMessage($xml);
        return response()->json(['message' => 'Payment request sent', 'response' => $response]);
    }
}
