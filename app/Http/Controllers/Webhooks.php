<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use DivisionByZeroError;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;



class Webhooks extends Controller
{    private $apiUrl;
    private $headers;
    public function __construct()
    {
        $apiToken = env('TESTING_TOKEN'); // Set your API token here
//        file_put_contents('test.txt',$apiToken);
        $this->apiUrl = env('MONDAY_URL');
        $this->headers = ['Content-Type: application/json', 'Authorization: ' . $apiToken, 'API-Version: 2023-10'];
    }
    private function makeRequests($query){
        return @file_get_contents($this->apiUrl, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $this->headers,
                'content' => json_encode(['query' => $query]),
            ]
        ]));
    }
    public function node(Request $request): void
    {
     $boardId = $request->input('payload')['inputFields']['boardId'];
     $itemId = $request->input('payload')['inputFields']['itemId'];
     $sourceColumnId= $request->input('payload')['inputFields']['sourceColumnId'];
     $targetColumnId = $request->input('payload')['inputFields']['targetColumnId'];
     $transformationType = $request->input('payload')['inputFields']['transformationType'];
     $query ="{ items ( ids: [\"$itemId\"]) { column_values(ids:[\"$sourceColumnId\"]) { value } } }";
     $response = json_decode($this->makeRequests($query),true);
     $text = json_decode($response['data']['items'][0]["column_values"][0]["value"]);
     $transformedText = strtolower($text);
     $mutation = "mutation {change_simple_column_value(board_id: $boardId ,item_id: $itemId,column_id:\"$targetColumnId\",value: \"$transformedText\"){id name created_at updated_at creator{id name}}}";
     $changedValue = json_decode($this->makeRequests($mutation),true);
        try {
            $decodedToken = JWT::decode($request->header('Authorization'), new Key(env('MONDAY_SIGNING_SECRET'),'HS256'),);
            file_put_contents('test.txt',$decodedToken->nbf);

        }catch (\Exception $e){
            file_put_contents('test.txt',$e);
        }

    }
    public function webhookListener(Request $request)
    {
        $challenge = $request->input('challenge');
        $event = $request->input('event');
        if($event){
            $data = [
                'boardId' => $event['boardId'],
                'userId' => $event['userId'],
                'columnTitle' => $event['columnTitle'],
                'value' => $event['value']['value'],
//                'changedAt' => now(),
            ];
            try {
                Webhook::create($data);
            }
            catch (DivisionByZeroError $e){
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
        return response()->json(["challenge" => $challenge]);
    }
}
