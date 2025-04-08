<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionController
 *
 * Controlador para gestionar las transacciones del usuario.
 * Proporciona métodos para crear, leer, actualizar, eliminar y listar transacciones.
 */
class TransactionController extends Controller
{
    /**
     * Muestra una lista de las transacciones del usuario autenticado.
     * Se pueden aplicar filtros como fecha, categoría, tipo de transacción (ingreso/gasto).
     *
     * @param  Request  $request  Filtros y parámetros de paginación
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con las transacciones paginadas
     */
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        // Filtrado por rango de fechas
        $query->when($request->filled('start_date') && $request->filled('end_date'), fn($q) =>
            $q->whereBetween('date', [$request->start_date, $request->end_date])
        )->when($request->filled('start_date') && !$request->filled('end_date'), fn($q) =>
            $q->where('date', '>=', $request->start_date)
        )->when(!$request->filled('start_date') && $request->filled('end_date'), fn($q) =>
            $q->where('date', '<=', $request->end_date)
        );

        // Filtrado por categoría
        $query->when($request->filled('category_id'), fn($q) =>
            $q->where('category_id', $request->category_id)
        );

        // Filtrado por tipo de transacción (ingreso o gasto)
        $query->when($request->filled('type'), function ($q) use ($request) {
            match ($request->type) {
                'income' => $q->where('amount', '>', 0),
                'expense' => $q->where('amount', '<', 0),
                default => null,
            };
        });

        // Ordenamiento y paginación
        $transactions = $query
            ->orderBy($request->get('sort_by', 'date'), $request->get('sort_direction', 'desc'))
            ->paginate($request->get('per_page', 15), ['*'], 'page', $request->get('page', 1));

        return response()->json($transactions);
    }

    /**
     * Crea una nueva transacción para el usuario autenticado.
     *
     * @param  Request  $request  Datos de la transacción a crear
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con la transacción creada
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        // Crear la transacción
        $transaction = Transaction::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    /**
     * Muestra los detalles de una transacción específica.
     *
     * @param  string  $id  ID de la transacción
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con la transacción encontrada
     */
    public function show(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        return response()->json($transaction);
    }

    /**
     * Actualiza una transacción existente del usuario autenticado.
     *
     * @param  Request  $request  Datos de la transacción a actualizar
     * @param  string   $id       ID de la transacción a actualizar
     * @return \Illuminate\Http\JsonResponse  Respuesta JSON con la transacción actualizada
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'amount' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date',
        ]);

        // Actualizar la transacción
        $transaction->update($validated);

        return response()->json($transaction);
    }

    /**
     * Elimina una transacción del usuario autenticado.
     *
     * @param  string  $id  ID de la transacción a eliminar
     * @return \Illuminate\Http\Response  Respuesta con un estado vacío (204 No Content)
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->noContent();
    }
}