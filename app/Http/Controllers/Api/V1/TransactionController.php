<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Transaction::where('user_id', Auth::id())->get());
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::where('user_id', Auth::id())->findOrFail($id);
        $transaction->delete();

        return response()->noContent();
    }
}
