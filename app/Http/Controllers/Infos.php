<?php

namespace App\Http\Controllers;

use App\Models\Info;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Infos extends Controller
{
    private $apiUrl;
    private $headers;
    public function __construct()
    {
        $apiToken = env('MONDAY_TOKEN'); // Set your API token here
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

    public function getItems(){
        $query = "{boards  {id items_page{items{id name column_values{id value text type }}}}}";
        $data = $this->makeRequests($query);
        $responseContent = json_decode($data, true);

        return response()->json($responseContent);
    }

    public function updateStatus(Request $request){
        $new_status = $request->input('new_status') ?? "Done";
        $boardId = $request->input('boardId');
        $item_id = $request->input('item_id');
        $mutation = "mutation {change_simple_column_value(board_id: $boardId ,item_id: $item_id,column_id:\"status\",value: \"$new_status\"){id name created_at updated_at creator{id name}}}";
        $response = json_decode($this->makeRequests($mutation),true);
        if (isset($response['data']['change_simple_column_value'])) {
            $data = $this->returnResponse($response,'change_simple_column_value');
            Info::create($data);
        }
        return response()->json($response);

    }
    public function updateItems(Request $request):JsonResponse
    {
        $boardId = $request->input('boardId');
        $item_id = $request->input('item_id');
        $new_name = $request->input('new_name') ?? "";
        $new_desc = $request->input('new_desc') ?? "";

        $mutation = "mutation {change_multiple_column_values(board_id: $boardId ,item_id: $item_id,column_values:\" { \\\"name\\\" : \\\"$new_name\\\" ,\\\"description\\\" : \\\"$new_desc\\\"} \", ){id name created_at updated_at creator{id name}}}";
        $response = json_decode($this->makeRequests($mutation),true);

        if (isset($response['data']['change_multiple_column_values'])) {

            $data = $this->returnResponse($response,'change_multiple_column_values');
            Info::create($data);
        }
        return response()->json($response);
    }
    public function deleteItems(Request $request)
    {
        $item_id = $request->input('itemId');


        $mutation = "mutation { delete_item(item_id:$item_id){id name created_at updated_at creator{id name}} }";

        $this->makeRequests($mutation);
        $response = json_decode($this->makeRequests($mutation),true);
        if (isset($response['data']['delete_item'])) {

            $data = $this->returnResponse($response,'delete_item');
            Info::create($data);
        }

        return response()->json($response);
    }
    private function returnResponse($response,$mutation_type): array
    {
        $itemData = $response['data']["$mutation_type"];

        // Extract the relevant values
        $mondayId = $itemData['id'];
        $name = $itemData['name'];
        $created_at = date("Y-m-d H:i:s",strtotime($itemData['created_at']));
        $updated_at = date("Y-m-d H:i:s",strtotime($itemData['updated_at']));
        $creatorName = $itemData['creator']['name'];
        $creatorId = $itemData['creator']['id'];

        return [
            'mondayId' => $mondayId,
            'name' => $name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'creatorName' => $creatorName,
            'creatorId' => $creatorId,
        ];
    }

    public function createIssue(Request $request)
    {
        $name = $request->input('name');
        $boardId = $request->input('boardId');
        $desc = $request->input('desc');
        $status= $request->input('status');

        $mutation = "mutation {create_item(board_id: $boardId ,item_name: \"$name\",column_values:  \"{\\\"status\\\":\\\"$status\\\",\\\"description\\\":\\\"$desc\\\"}\"  ){id name created_at updated_at creator{id name}}}";
        $response = json_decode($this->makeRequests($mutation),true);

        if (isset($response['data']['create_item'])) {
            $data = $this->returnResponse($response,'create_item');
            Info::create($data);
        }
        return response()->json($response);
    }
}
