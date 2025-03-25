<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CategoryController
 *
 * This controller handles API requests related to categories.
 * It provides methods for creating, reading, updating, and deleting category records.
 */
class CategoryController extends Controller
{
    /**
     * index
     *
     * Retrieves and returns a list of all categories.
     *
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the list of categories.
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    /**
     * store
     *
     * Validates and stores a new category in the database.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the category data.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
            'type' => 'required|in:income,expense',
        ]);

        $category = Category::create($request->all());

        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * show
     *
     * Retrieves and returns the details of a specific category.
     *
     * @param  string  $id The ID of the category to be shown.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the category details.
     */
    public function show(string $id)
    {
        return response()->json(Category::findOrFail($id));
    }

    /**
     * update
     *
     * Validates and updates an existing category in the database.
     *
     * @param  \Illuminate\Http\Request  $request The request containing the updated category data.
     * @param  string  $id The ID of the category to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response containing the updated category.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'string|unique:categories,name,' . $id,
            'type' => 'in:income,expense',
        ]);

        $category->update($request->only('name', 'type'));

        return response()->json($category);
    }

    /**
     * destroy
     *
     * Deletes a specific category from the database.
     *
     * @param  string  $id The ID of the category to be deleted.
     * @return \Illuminate\Http\JsonResponse Returns an empty JSON response.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json();
    }
}