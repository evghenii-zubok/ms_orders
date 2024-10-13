<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessOrder;
use App\Jobs\OrderShipped;
use App\Jobs\OrderCompleted;
use App\Jobs\OrderCancelled;
use Exception;

class OrderController extends Controller
{
    /**
    * @OA\Post(
    *     path="/api/v1/order",
    *     operationId="store",
    *     tags={"Orders CRUD"},
    *     summary="Create new order.",
    *
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="user_id",
    *                     type="bigInteger",
    *                     default="1",
    *                 ),
    *                 @OA\Property(
    *                     property="total_amount",
    *                     type="decimal",
    *                     default="15",
    *                 ),
    *                 @OA\Property(
    *                     description="Array of products",
    *                     property="product_list",
    *                     type="array",
    *                     collectionFormat="multi",
    *                     @OA\Items(
    *                         type="object",
    *                         @OA\Property(property="product_id", type="integer", default="1"),
    *                         @OA\Property(property="ean", type="string", default="0123456789123"),
    *                         @OA\Property(property="name", type="string", default="Goleador"),
    *                         @OA\Property(property="qty", type="integer", default="150"),
    *                         @OA\Property(property="price", type="double", default="0.1"),
    *                     ),
    *                 ),
    *             ),
    *         ),
    *     ),
    *
    *     @OA\Response(
    *         response=200,
    *         description="Successfull operation",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 type="boolean",
    *                 default="true",
    *                 description="Status",
    *                 property="status",
    *             ),
    *             @OA\Property(
    *                 type="object",
    *                 property="data",
    *             ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Unauthorized",
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     ),
    * )
    */
    public function store(Request $request) : JsonResponse
    {
        // 1. verifico la bontà del dato
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'product_list' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status' => false,
                'data' => $validator->errors()
            ], 400);
        }

        // 2. suppongo che le disponibilità siano giuste sennò lato frontend non sarebbero stati disponibili

        // 3. inserisco l'ordine

        try {

            $order = Order::create([
                'user_id' => $request->input('user_id'),
                'order_date' => Carbon::now(),
                'product_list' => json_encode($request->input('product_list')),
                'total_amount' => $request->input('total_amount'),
            ]);
    
            // 4. notifico che l'ordine è stato inserito
            // -  gli altri servizi dovranno avere lo stesso Job inmplementato con le logiche come ad esempio:
            // -  servizio di mailing riceverà la notifica è inviarà la mail con i dettagli dell'ordine
            // -  servizio di wharehouse deve diminuire la giacenza dei prodotti
            ProcessOrder::dispatch($order->toArray());
            // dispatch(new ProcessOrder($order));
        
        } catch (Exception $e) {

            return response()->json([
                'status' => false,
                'data' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order/{id}",
     *     operationId="show",
     *     tags={"Orders CRUD"},
     *     summary="Get order by ID",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order to search ID.",
     *         @OA\Schema(type="integer"),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfull operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 type="boolean",
     *                 default="true",
     *                 description="Status",
     *                 property="status",
     *             ),
     *             @OA\Property(
     *                 type="object",
     *                 description="Requested order in json format",
     *                 property="data",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     * )
     */
    public function show(Request $request, int $order) : JsonResponse
    {
        $item = Order::find($order);

        if (!$item) {

            return response()->json([
                'status' => false,
                'data' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $item
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/order/{id}",
     *     operationId="update",
     *     tags={"Orders CRUD"},
     *     summary="Update order",
     *     description="Statuses list for update: Processing, Shipped, Completed, Cancelled",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID.",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     default="Shipped",
     *                     enum={"Processing","Shipped","Completed","Cancelled"}
     *                 ),
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfull operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 type="boolean",
     *                 default="true",
     *                 description="Status",
     *                 property="status",
     *             ),
     *             @OA\Property(
     *                 type="object",
     *                 description="Updated order in json format",
     *                 property="data",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function update(Request $request, int $order) : JsonResponse
    {
        $item = Order::find($order);

        if (!$order) {

            return response()->json([
                'status' => false,
                'data' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Processing,Shipped,Completed,Cancelled'
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'status' => false,
                'data' => $validator->errors()
            ], 400);
        }

        $item->update(['status' => $request->input('status')]);

        switch ($item->status) {

            case "Shipped":
                // Invia la notifica che la spedizione è stata presa in carico
                OrderShipped::dispatch($item->toArray());
                break;

            case "Completed":
                // Invia la mail chiedendo una recensione o cose del genere
                OrderCompleted::dispatch($item->toArray());
                break;

            case "Shipped":
                // Invia la richiesta di rimborso
                OrderCancelled::dispatch($item->toArray());
                break;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'status' => $item->status
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/order/{id}",
     *     operationId="destroy",
     *     tags={"Orders CRUD"},
     *     summary="Delete order",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfull operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 type="boolean",
     *                 default="true",
     *                 description="Status",
     *                 property="status",
     *             ),
     *             @OA\Property(
     *                 type="string",
     *                 default="oDeletedk",
     *                 property="data",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy(Request $request, int $order) : JsonResponse
    {
        $item = Order::find($order);

        if (!$item) {

            return response()->json([
                'status' => false,
                'data' => 'Order not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'status' => true,
            'data' => 'Deleted'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order/user/{id}",
     *     operationId="getOrdersByUser",
     *     tags={"Orders CRUD"},
     *     summary="Get order by User ID",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer"),
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfull operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 type="boolean",
     *                 default="true",
     *                 description="Status",
     *                 property="status",
     *             ),
     *             @OA\Property(
     *                 type="array",
     *                 collectionFormat="multi",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product_id", type="integer", default="1"),
     *                     @OA\Property(property="ean", type="string", default="0123456789123"),
     *                     @OA\Property(property="name", type="string", default="Goleador"),
     *                     @OA\Property(property="qty", type="integer", default="150"),
     *                     @OA\Property(property="price", type="double", default="0.1"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     * )
     */
    public function getOrdersByUser(Request $request, int $user) : JsonResponse
    {
        $orders = Order::where('user_id', $user)
            ->get()
            ->toArray();

        return response()->json([
            'status' => true,
            'data' => $orders
        ]);
    }
}
