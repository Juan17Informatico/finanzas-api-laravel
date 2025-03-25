<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        return response()->json(Budget::where('user_id', Auth::id())->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:0',
        ]);

        $budget = Budget::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'limit_amount' => $request->limit_amount,
        ]);

        return response()->json($budget, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($budget);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'category_id' => 'exists:categories,id',
            'limit_amount' => 'numeric|min:0',
        ]);

        $budget->update($request->only('category_id', 'limit_amount'));

        return response()->json($budget);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        $budget->delete();

        return response()->noContent();
    }
}
