<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador responsable de gestionar los presupuestos del usuario autenticado.
 * Incluye operaciones CRUD, generación de reportes y validaciones personalizadas.
 */
class BudgetController extends Controller
{
    /**
     * Muestra una lista paginada de presupuestos del usuario autenticado.
     *
     * @param Request $request Parámetros de paginación (per_page, page).
     * @return \Illuminate\Http\JsonResponse Lista paginada de presupuestos.
     */
    public function index(Request $request)
    {
        $budgets = $this->getUserBudgetsQuery()
            ->paginate($request->get('per_page', 15), ['*'], 'page', $request->get('page', 1));

        return response()->json($budgets);
    }

    /**
     * Crea un nuevo presupuesto para el usuario autenticado.
     *
     * @param Request $request Datos validados: category_id, limit_amount.
     * @return \Illuminate\Http\JsonResponse Presupuesto creado o error si ya existe para la categoría.
     */
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

    /**
     * Muestra un presupuesto específico del usuario autenticado.
     *
     * @param string $id ID del presupuesto.
     * @return \Illuminate\Http\JsonResponse Presupuesto encontrado.
     */
    public function show(string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);
        return response()->json($budget);
    }

    /**
     * Actualiza un presupuesto del usuario autenticado.
     *
     * @param Request $request Datos validados: category_id, limit_amount.
     * @param string $id ID del presupuesto a actualizar.
     * @return \Illuminate\Http\JsonResponse Presupuesto actualizado o error si ya existe esa categoría.
     */
    public function update(Request $request, string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'limit_amount' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['category_id']) && $this->budgetExistsForCategory($validated['category_id'], $id)) {
            return response()->json([
                'message' => 'Ya tienes un presupuesto para esta categoría'
            ], Response::HTTP_BAD_REQUEST);
        }

        $budget->update($validated);

        return response()->json($budget);
    }

    /**
     * Elimina un presupuesto del usuario autenticado.
     *
     * @param string $id ID del presupuesto a eliminar.
     * @return \Illuminate\Http\Response Respuesta vacía con código 204.
     */
    public function destroy(string $id)
    {
        $budget = $this->getUserBudgetsQuery()->findOrFail($id);
        $budget->delete();

        return response()->noContent();
    }

    /**
     * Genera estadísticas y retorna una lista paginada de los presupuestos del usuario.
     *
     * @param Request $request Parámetros de paginación (per_page, page).
     * @return \Illuminate\Http\JsonResponse Estadísticas y presupuestos.
     */
    public function reports(Request $request)
    {
        $budgets = $this->getUserBudgetsQuery()->with('category')->get();

        if ($budgets->isEmpty()) {
            return response()->json([
                'message' => 'No hay presupuestos registrados para generar el reporte.'
            ], Response::HTTP_NOT_FOUND);
        }

        $totalBudget = $budgets->sum('limit_amount');
        $averageBudget = $budgets->avg('limit_amount');
        $highestBudget = $budgets->max('limit_amount');
        $lowestBudget = $budgets->min('limit_amount');

        // Agrupar presupuestos por categoría
        $budgetsByCategory = $budgets->groupBy('category.name')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total' => $items->sum('limit_amount')
            ];
        });

        $paginated = $budgets->forPage(
            $request->get('page', 1),
            $request->get('per_page', 10)
        )->values();

        return response()->json([
            'total_budget' => $totalBudget,
            'average_budget' => $averageBudget,
            'max_budget' => $highestBudget,
            'min_budget' => $lowestBudget,
            'budgets_by_category' => $budgetsByCategory,
            'data' => $paginated,
        ]);
    }

    /** ========== MÉTODOS PRIVADOS ========== */

    /**
     * Obtiene la consulta base de los presupuestos del usuario autenticado.
     *
     * @return \Illuminate\Database\Eloquent\Builder Consulta filtrada por el usuario.
     */
    private function getUserBudgetsQuery(): Builder
    {
        return Budget::where('user_id', Auth::id());
    }

    /**
     * Verifica si ya existe un presupuesto para una categoría específica.
     *
     * @param int $categoryId ID de la categoría.
     * @param string|null $excludeId ID de presupuesto a excluir (en caso de actualización).
     * @return bool True si ya existe, false si no.
     */
    private function budgetExistsForCategory(int $categoryId, ?string $excludeId = null): bool
    {
        $query = $this->getUserBudgetsQuery()->where('category_id', $categoryId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Retorna una respuesta de error cuando ya existe un presupuesto para la categoría.
     *
     * @return \Illuminate\Http\JsonResponse Mensaje de error 400.
     */
    private function categoryAlreadyExistsResponse()
    {
        return response()->json([
            'message' => 'Ya existe un presupuesto para esta categoría'
        ], Response::HTTP_BAD_REQUEST);
    }
}