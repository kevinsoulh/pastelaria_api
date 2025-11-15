<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OrderCreated;

class OrderController extends Controller
{
    use ApiResponseTrait;
    protected OrderRepositoryInterface $orderRepository;
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'customer_id', 
            'status', 
            'date_from', 
            'date_to', 
            'sort_order'
        ]);
        
        $orders = $this->orderRepository->getWithRelations($filters);
        
        return $this->successResponseWithPagination($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $customer = $this->customerRepository->findOrFail($validatedData['customer_id']);
            
            $order = $this->orderRepository->createWithProducts(
                $customer,
                $validatedData['products'],
                ['notes' => $validatedData['notes'] ?? null]
            );
            
            try {
                Mail::send(new OrderCreated($order));
            } catch (\Exception $e) {
                Log::error('Failed to send order email: ' . $e->getMessage());
            }
            
            return $this->createdResponse($order, 'Order created successfully');
            
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create order', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->findOrFail($id, ['customer', 'products']);
        return $this->successResponse($order);
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findOrFail($id);
        $updatedOrder = $this->orderRepository->update($order, $request->validated());
        
        $updatedOrder->load(['customer', 'products']);
        
        return $this->successResponse($updatedOrder, 'Order updated successfully');
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderRepository->findOrFail($id);
        
        if (!$order->canBeCancelled()) {
            return $this->errorResponse('Cannot delete order in current status', 422);
        }
        
        $this->orderRepository->delete($order);
        
        return $this->successResponse(null, 'Order deleted successfully');
    }

    /**
     * Get order statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->orderRepository->getStatistics();
        return $this->successResponse($stats);
    }
}
