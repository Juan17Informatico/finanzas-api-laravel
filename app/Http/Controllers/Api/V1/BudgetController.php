<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * BudgetController
 *
 * This controller handles API requests related to user budgets.
 * It provides methods for creating, reading, updating, and deleting budget records,
 * ensuring all operations are scoped to the authenticated user.
 */
class BudgetController extends Controller
{
    /**
     * index
     *
     * Retrieves and returns a list of all budgets belonging to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the list of budgets.
     */
    public function index()
    {
        return response()->json(Budget::where('user_id', Auth::id())->get());
    }

    /**
     * store
     *
     * Validates and stores a new budget in the database for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the budget data.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the created budget.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:0',
        ]);

        // Validar que el usuario no tenga un presupuesto para la misma categoría
        if (Budget::where('user_id', Auth::id())->where('category_id', $request->category_id)->exists()) {
            return response()->json([
                'message' => 'Ya existe un presupuesto para esta categoría'
            ], Response::HTTP_BAD_REQUEST);
        }

        $budget = Budget::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'limit_amount' => $request->limit_amount,
        ]);

        return response()->json($budget, Response::HTTP_CREATED);
    }

    /**
     * show
     *
     * Retrieves and returns the details of a specific budget belonging to the authenticated user.
     *
     * @param  string  $id The ID of the budget to be shown.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the budget details.
     */
    public function show(string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($budget);
    }

    /**
     * update
     *
     * Validates and updates an existing budget belonging to the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the updated budget data.
     * @param  string  $id The ID of the budget to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the updated budget.
     */
    public function update(Request $request, string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'category_id' => 'exists:categories,id',
            'limit_amount' => 'numeric|min:0',
        ]);

        // Validar que la categoría no esté duplicada en otro presupuesto del usuario
        if ($request->has('category_id')) {
            if (Budget::where('user_id', Auth::id())
                ->where('category_id', $request->category_id)
                ->where('id', '!=', $id)
                ->exists()
            ) {
                return response()->json([
                    'message' => 'Ya tienes un presupuesto para esta categoría'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $budget->update($request->only('category_id', 'limit_amount'));

        return response()->json($budget);
    }

    /**
     * destroy
     *
     * Deletes a specific budget belonging to the authenticated user.
     *
     * @param  string  $id The ID of the budget to be deleted.
     * @return \Illuminate\Http\Response Returns an empty response with a 204 No Content status code.
     */
    public function destroy(string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);
        $budget->delete();

        return response()->noContent();
    }

    public function reports()
    {
        $userId = Auth::id();

        $budgets = Budget::where('user_id', $userId)->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'message' => 'No hay presupuestos registrados para generar el reporte.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Cálculo de estadísticas
        $totalBudget = $budgets->sum('limit_amount');
        $averageBudget = $budgets->avg('limit_amount');
        $maxBudget = $budgets->max('limit_amount');
        $minBudget = $budgets->min('limit_amount');

        // Agrupación de presupuestos por categoría
        $budgetsByCategory = $budgets->map(function ($budget) {
            return [
                'category_id' => $budget->category_id,
                'limit_amount' => $budget->limit_amount,
            ];
        });

        return response()->json([
            'total_budget' => $totalBudget,
            'average_budget' => $averageBudget,
            'max_budget' => $maxBudget,
            'min_budget' => $minBudget,
            'budgets_by_category' => $budgetsByCategory,
        ], Response::HTTP_OK);
    }
}
