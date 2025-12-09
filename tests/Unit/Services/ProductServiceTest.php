<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected ProductRepositoryInterface|MockInterface $repository;
    protected ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_all_products_without_filters(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        
        $this->repository
            ->shouldReceive('getAll')
            ->once()
            ->with(15)
            ->andReturn($paginator);

        $result = $this->service->getAllProducts([], 15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_all_products_with_status_filter(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        
        $this->repository
            ->shouldReceive('getActive')
            ->once()
            ->with(15)
            ->andReturn($paginator);

        $result = $this->service->getAllProducts(['status' => 'active'], 15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_all_products_with_search_filter(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        
        $this->repository
            ->shouldReceive('search')
            ->once()
            ->with('laptop', 15)
            ->andReturn($paginator);

        $result = $this->service->getAllProducts(['search' => 'laptop'], 15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_product_by_id_when_found(): void
    {
        $product = Product::factory()->make(['id' => 1]);
        
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($product);

        $result = $this->service->getProductById(1);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals(1, $result->id);
    }

    /** @test */
    public function it_throws_exception_when_product_not_found(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Product with ID 999 not found.');

        $this->service->getProductById(999);
    }

    /** @test */
    public function it_can_create_product_with_default_status(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
        ];

        $product = Product::factory()->make([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
            'status' => 'active',
        ]);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Test Product',
                'price' => 99.99,
                'stock' => 10,
                'status' => 'active',
            ])
            ->andReturn($product);

        $result = $this->service->createProduct($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('active', $result->status);
    }

    /** @test */
    public function it_can_create_product_with_provided_status(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10,
            'status' => 'inactive',
        ];

        $product = Product::factory()->make($data);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($product);

        $result = $this->service->createProduct($data);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('inactive', $result->status);
    }

    /** @test */
    public function it_can_update_existing_product(): void
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $product->shouldReceive('fresh')->once()->andReturn($product);
        
        $updateData = ['name' => 'Updated Name', 'price' => 150.00];

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($product);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($product, $updateData)
            ->andReturn(true);

        $result = $this->service->updateProduct(1, $updateData);

        $this->assertInstanceOf(Product::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_updating_nonexistent_product(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->updateProduct(999, ['name' => 'Updated']);
    }

    /** @test */
    public function it_can_delete_existing_product(): void
    {
        $product = Product::factory()->make(['id' => 1]);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($product);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with($product)
            ->andReturn(true);

        $result = $this->service->deleteProduct(1);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_nonexistent_product(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->deleteProduct(999);
    }

    /** @test */
    public function it_prioritizes_status_over_search_filter(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);
        
        // Status filter is checked first in the service logic
        $this->repository
            ->shouldReceive('getActive')
            ->once()
            ->with(15)
            ->andReturn($paginator);

        // When both filters are present, status takes priority (checked first)
        $result = $this->service->getAllProducts([
            'status' => 'active',
            'search' => 'laptop',
        ], 15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}

