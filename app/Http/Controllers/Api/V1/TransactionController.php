<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TransactionController
 *
 * This controller handles API requests related to user transactions.
 * It provides methods for creating, reading, updating, and deleting transaction records,
 * ensuring all operations are scoped to the authenticated user.
 */
class TransactionController extends Controller
{
    /**
     * index
     *
     * Retrieves and returns a list of all transactions belonging to the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the list of transactions.
     */
    public function index()
    {
        return response()->json(Transaction::where('user_id', Auth::id())->get());
    }

    /**
     * store
     *
     * Validates and stores a new transaction in the database for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the transaction data.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the created transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return response()->json($transaction, Response::HTTP_CREATED);
    }

    /**
     * show
     *
     * Retrieves and returns the details of a specific transaction belonging to the authenticated user.
     *
     * @param  string  $id The ID of the transaction to be shown.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the transaction details.
     */
    public function show(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * update
     *
     * Validates and updates an existing transaction belonging to the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the updated transaction data.
     * @param  string  $id The ID of the transaction to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the updated transaction.
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'category_id' => 'exists:categories,id',
            'amount' => 'numeric',
            'description' => 'string',
            'date' => 'date',
        ]);

        $transaction->update($request->only('category_id', 'amount', 'description', 'date'));

        return response()->json($transaction);
    }

    /**
     * destroy
     *
     * Deletes a specific transaction belonging to the authenticated user.
     *
     * @param  string  $id The ID of the transaction to be deleted.
     * @return \Illuminate\Http\Response Returns an empty response with a 204 No Content status code.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->noContent();
    }
}