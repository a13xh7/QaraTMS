<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Expense;

class SummaryController extends Controller
{
    /**
     * Get summary of expenses by category for a given month
     */
    public function getSummary($month)
    {
        // Get all categories
        $categories = Category::all();

        // Get all expenses for the given month
        $expenses = Expense::whereMonth('created_at', '=', $month)->get();

        $summary = [];

        foreach ($categories as $category) {
            $totalSpent = $expenses->where('category_id', $category->id)->sum('amount');
            $status = $totalSpent > $category->limit ? 'over' : 'under';

            $summary[$category->name] = [
                'totalSpent' => $totalSpent,
                'status' => $status
            ];
        }

        return response()->json($summary);
    }
}
