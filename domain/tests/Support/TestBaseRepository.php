<?php

namespace Tests\Support;

use App\Models\Product;
use App\Repositories\BaseRepository;

/**
 * Concrete implementation of BaseRepository for testing purposes
 */
class TestBaseRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Product());
    }
}