<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use ApiResponseTrait;
    
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['name', 'email']);

        $customers = $this->customerRepository->getWithOrders($filters);
        
        return $this->successResponseWithPagination($customers, 'Customers retrieved successfully');
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = $this->customerRepository->create($request->validated());
            
            return $this->createdResponse($customer, 'Customer created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->findOrFail($id, ['orders.products']);
            return $this->successResponse($customer, 'Customer retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Customer not found');
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->findOrFail($id);
            $updatedCustomer = $this->customerRepository->update($customer, $request->validated());
            
            return $this->updatedResponse($updatedCustomer, 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update customer: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $customer = $this->customerRepository->findOrFail($id);
            $this->customerRepository->delete($customer);
            
            return $this->deletedResponse('Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete customer: ' . $e->getMessage(), 500);
        }
    }
}
