<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = $this->getUserBudgetsQuery()
            ->paginate($request->get('per_page', 15), ['*'], 'page', $request->get('page', 1));

        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:0',
        ]);

        if ($this->budgetExistsForCategory($validated['category_id'])) {
            return $this->categoryAlreadyExistsResponse();
        }

        $budget = Budget::create([
            'user_id' => Auth::id(),
            ...$validated,
        ]);

        return response()->json($budget, Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);
        return response()->json($budget);
    }

    public function update(Request $request, string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'limit_amount' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['category_id']) && $this->budgetExistsForCategory($validated['category_id'], $id)) {
            return $this->categoryAlreadyExistsResponse();
        }

        $budget->update($validated);

        return response()->json($budget);
    }

    public function destroy(string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);
        $budget->delete();

        return response()->noContent();
    }

    public function reports(Request $request)
    {
        $budgets = $this->getUserBudgetsQuery()->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'message' => 'No hay presupuestos registrados para generar el reporte.'
            ], Response::HTTP_NOT_FOUND);
        }

        $totalBudget = $budgets->sum('limit_amount');
        $averageBudget = $budgets->avg('limit_amount');
        $highestBudget = $budgets->max('limit_amount');
        $lowestBudget = $budgets->min('limit_amount');

        $paginated = $budgets->forPage(
            $request->get('page', 1),
            $request->get('per_page', 10)
        )->values();

        return response()->json([
            'statistics' => [
                'total' => $totalBudget,
                'average' => $averageBudget,
                'highest' => $highestBudget,
                'lowest' => $lowestBudget,
            ],
            'data' => $paginated,
        ]);
    }

    /** ========== MÉTODOS PRIVADOS ========== */

    private function getUserBudgetsQuery()
    {
        return Budget::where('user_id', Auth::id());
    }

    private function budgetExistsForCategory(int $categoryId, ?string $excludeId = null): bool
    {
        $query = $this->getUserBudgetsQuery()->where('category_id', $categoryId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function categoryAlreadyExistsResponse()
    {
        return response()->json([
            'message' => 'Ya existe un presupuesto para esta categoría'
        ], Response::HTTP_BAD_REQUEST);
    }
}
