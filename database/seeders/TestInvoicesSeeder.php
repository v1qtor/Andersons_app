<?php

namespace Database\Seeders;

use App\Models\Receipt;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class TestInvoicesSeeder extends Seeder
{
    public function run(): void
    {
        // Find or get a chef user
        $chef = User::whereHas('role', function ($query) {
            $query->where('name', 'Chef');
        })->first();

        if (!$chef) {
            return;
        }

        $categories = Category::all();

        // Create pending invoices
        Receipt::create([
            'user_id' => $chef->id,
            'category_id' => $categories->where('name', 'Meals & Hospitality')->first()?->id,
            'bill_date' => now()->subDays(5)->toDateString(),
            'description' => 'Grocery shopping for kitchen inventory',
            'name' => null,
            'amount' => 125.50,
            'file_path' => 'invoices/sample.pdf',
            'upload_date' => now()->subDays(5),
            'is_paid' => false,
            'paid_date' => null,
        ]);

        Receipt::create([
            'user_id' => $chef->id,
            'category_id' => $categories->where('name', 'Equipment & Tools')->first()?->id,
            'bill_date' => now()->subDays(3)->toDateString(),
            'description' => 'Kitchen utensils and equipment replacement',
            'name' => null,
            'amount' => 89.99,
            'file_path' => 'invoices/sample2.pdf',
            'upload_date' => now()->subDays(3),
            'is_paid' => false,
            'paid_date' => null,
        ]);

        // Create a reimbursed invoice
        Receipt::create([
            'user_id' => $chef->id,
            'category_id' => $categories->where('name', 'Office Supplies')->first()?->id,
            'bill_date' => now()->subDays(10)->toDateString(),
            'description' => 'Kitchen supplies and materials',
            'name' => null,
            'amount' => 56.75,
            'file_path' => 'invoices/sample3.pdf',
            'upload_date' => now()->subDays(10),
            'is_paid' => true,
            'paid_date' => now()->subDays(2),
        ]);
    }
}
