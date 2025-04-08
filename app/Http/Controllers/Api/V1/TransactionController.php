<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', Auth::id());

        $query->when($request->filled('start_date') && $request->filled('end_date'), fn($q) =>
            $q->whereBetween('date', [$request->start_date, $request->end_date])
        )->when($request->filled('start_date') && !$request->filled('end_date'), fn($q) =>
            $q->where('date', '>=', $request->start_date)
        )->when(!$request->filled('start_date') && $request->filled('end_date'), fn($q) =>
            $q->where('date', '<=', $request->end_date)
        );

        $query->when($request->filled('category_id'), fn($q) =>
            $q->where('category_id', $request->category_id)
        );

        $query->when($request->filled('type'), function ($q) use ($request) {
            match ($request->type) {
                'income' => $q->where('amount', '>', 0),
                'expense' => $q->where('amount', '<', 0),
                default => null,
            };
        });

        $transactions = $query
            ->orderBy($request->get('sort_by', 'date'), $request->get('sort_direction', 'desc'))
            ->paginate($request->get('per_page', 15), ['*'], 'page', $request->get('page', 1));

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $transaction = Transaction::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        return response()->json($transaction);
    }

    public function update(Request $request, string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'amount' => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date',
        ]);

        $transaction->update($validated);

        return response()->json($transaction);
    }

    public function destroy(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->noContent();
    }
}
