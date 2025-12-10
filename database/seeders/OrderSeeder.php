<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::where('status', 'active')->where('stock', '>', 0)->get();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run UserSeeder and ProductSeeder first.');
            return;
        }

        // Create orders with different statuses
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $paymentStatuses = ['pending', 'success', 'failed', 'cancelled'];

        // Create 20 orders
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Generate unique order ID
            $orderId = 'ORDER-' . time() . '-' . $user->id . '-' . uniqid();

            // Select random products for this order (1-4 products)
            $orderProducts = $products->random(rand(1, min(4, $products->count())));
            
            $totalAmount = 0;
            $orderItems = [];

            foreach ($orderProducts as $product) {
                $quantity = rand(1, min(3, $product->stock));
                $price = $product->price;
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'total_amount' => $totalAmount,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'dinger_transaction_id' => $paymentStatus === 'success' ? 'DINGER-' . uniqid() : null,
                'dinger_provider_name' => $paymentStatus === 'success' ? fake()->randomElement(['KBZ Pay', 'Wave Pay', 'AYA Pay', 'CB Pay']) : null,
                'dinger_method_name' => $paymentStatus === 'success' ? fake()->randomElement(['mobile', 'qr', 'online']) : null,
                'payment_completed_at' => $paymentStatus === 'success' ? now()->subDays(rand(1, 30)) : null,
                'payment_failed_at' => $paymentStatus === 'failed' ? now()->subDays(rand(1, 30)) : null,
                'payment_failure_reason' => $paymentStatus === 'failed' ? fake()->randomElement(['Insufficient funds', 'Payment timeout', 'User cancelled', 'Network error']) : null,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Add some delay to ensure unique timestamps for order_id
            usleep(1000);
        }

        $this->command->info('Created 20 orders with order items.');
    }
}

