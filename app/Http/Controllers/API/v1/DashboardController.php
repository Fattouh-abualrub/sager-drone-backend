<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $total_users = User::count();
        $total_categories = Category::count();
        $total_products = Product::count();

        $total_numbers = [
            $total_users,
            $total_products,
            $total_categories
        ];

        $last_months = [
            Carbon::now()->format('F'),
            Carbon::now()->subMonth()->format('F'),
            Carbon::now()->subMonths(2)->format('F')
        ];

        $monthly_data = [];
        // number 3 for how many month get
        for ($i = 2; $i >= 0; $i--) {

            $date = [Carbon::now()->subMonth($i)->startOfMonth(), Carbon::now()->subMonth($i)->endOfMonth()];

            $products_in_month = Product::GetTotalByMonth($date)
                ->selectRaw('year(created_at) as year ,month(created_at) as month ,count(*) as total')
                ->groupBy('year')
                ->groupBy('month')
                ->orderBy('year')
                ->orderBy('month')
                ->first();

            $users_in_month = User::GetTotalByMonth($date)
                ->selectRaw('year(created_at) as year ,month(created_at) as month ,count(*) as total')
                ->groupBy('year')
                ->groupBy('month')
                ->orderBy('year')
                ->orderBy('month')
                ->first();

            $categories_in_month = Category::GetTotalByMonth($date)
                ->selectRaw('year(created_at) as year ,month(created_at) as month ,count(*) as total')
                ->groupBy('year')
                ->groupBy('month')
                ->orderBy('year')
                ->orderBy('month')
                ->first();

            $monthly_data["products"][] = ($products_in_month) ? $products_in_month->total : 0;
            $monthly_data["users"][] = ($users_in_month) ? $users_in_month->total : 0;
            $monthly_data["categories"][] = ($categories_in_month) ? $categories_in_month->total : 0;
        }


        $response = [
            "status" => true,
            "total_numbers" => $total_numbers,
            "month_names" => $last_months,
            "monthly_total" => $monthly_data,
        ];

        return response($response, 200);
    }
}
