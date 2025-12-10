<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific demo products
        $demoProducts = [
            [
                'name' => 'Laptop Pro 15"',
                'description' => 'High-performance laptop with 16GB RAM and 512GB SSD',
                'price' => 1299.99,
                'stock' => 25,
                'image_url' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853',
                'status' => 'active',
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with long battery life',
                'price' => 29.99,
                'stock' => 150,
                'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db',
                'status' => 'active',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'RGB backlit mechanical keyboard with cherry switches',
                'price' => 149.99,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3',
                'status' => 'active',
            ],
            [
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader',
                'price' => 49.99,
                'stock' => 75,
                'image_url' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07',
                'status' => 'active',
            ],
            [
                'name' => 'Monitor 27" 4K',
                'description' => 'Ultra HD 4K monitor with HDR support',
                'price' => 399.99,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf',
                'status' => 'active',
            ],
            [
                'name' => 'Webcam HD',
                'description' => '1080p HD webcam with auto-focus and noise cancellation',
                'price' => 79.99,
                'stock' => 100,
                'image_url' => 'https://images.unsplash.com/photo-1587825147138-3462379b9e19',
                'status' => 'active',
            ],
            [
                'name' => 'USB Flash Drive 128GB',
                'description' => 'High-speed USB 3.0 flash drive with 128GB storage',
                'price' => 19.99,
                'stock' => 200,
                'image_url' => 'https://images.unsplash.com/photo-1600298881974-6be191ceeda1',
                'status' => 'active',
            ],
            [
                'name' => 'Laptop Stand',
                'description' => 'Adjustable aluminum laptop stand for better ergonomics',
                'price' => 39.99,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46',
                'status' => 'active',
            ],
            [
                'name' => 'Noise Cancelling Headphones',
                'description' => 'Premium wireless headphones with active noise cancellation',
                'price' => 249.99,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
                'status' => 'active',
            ],
            [
                'name' => 'External SSD 1TB',
                'description' => 'Portable external SSD with USB-C connection',
                'price' => 129.99,
                'stock' => 35,
                'image_url' => 'https://images.unsplash.com/photo-1591488320449-011701bb6704',
                'status' => 'active',
            ],
        ];

        foreach ($demoProducts as $product) {
            Product::create($product);
        }

        // Create additional random products using factory
        Product::factory(20)->active()->inStock()->create();
        
        // Create some inactive products
        Product::factory(5)->inactive()->create();
        
        // Create some out of stock products
        Product::factory(3)->active()->outOfStock()->create();
    }
}

