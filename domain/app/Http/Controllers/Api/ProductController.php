<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $this->getFilters($request);
        
        if ($this->hasFilters($filters)) {
            $products = $this->applyFilters($filters);
            return $this->successResponse($products->toArray());
        }
        
        $paginator = $this->productRepository->paginate(15);
        return $this->successResponseWithPagination($paginator);
    }

    /**
     * Extract filters from request
     */
    private function getFilters(Request $request): array
    {
        return [
            'name' => $request->get('name'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'category' => $request->get('category'),
            'available' => $request->get('available')
        ];
    }

    /**
     * Check if any filters are present
     */
    private function hasFilters(array $filters): bool
    {
        return !empty(array_filter($filters));
    }

    /**
     * Apply the appropriate filter based on request
     */
    private function applyFilters(array $filters)
    {
        if (!empty($filters['name'])) {
            return $this->productRepository->findByName($filters['name']);
        }
        
        if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
            return $this->productRepository->findByPriceRange(
                (float) $filters['min_price'],
                (float) $filters['max_price']
            );
        }
        
        if (!empty($filters['category'])) {
            return Product::where('category', $filters['category'])->get();
        }
        
        if (!empty($filters['available'])) {
            return Product::available()->get();
        }
        
        return Product::all();
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());
        return $this->createdResponse($product, 'Product created successfully');
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id, ['orders.customer']);
        return $this->successResponse($product);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);
        $updatedProduct = $this->productRepository->update($product, $request->validated());
        return $this->successResponse($updatedProduct, 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);
        
        // Check if product has orders using repository
        $productWithOrders = $this->productRepository->find($id, ['orders']);
        if ($productWithOrders->orders->isNotEmpty()) {
            return $this->errorResponse('Cannot delete product that has orders', 422);
        }
        
        $this->productRepository->delete($product);
        return $this->successResponse(null, 'Product deleted successfully');
    }
}
