<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        $category = Category::create($validated);

        return response()->json($category, Response::HTTP_CREATED);
    }

    public function show(string $id)
    {
        return response()->json($this->findCategory($id));
    }

    public function update(Request $request, string $id)
    {
        $category = $this->findCategory($id);

        $validated = $this->validateCategory($request, $id);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(string $id)
    {
        $category = $this->findCategory($id);
        $category->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * validateCategory
     *
     * Centraliza la validación de las categorías.
     */
    private function validateCategory(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'name' => ['string', 'required', 'unique:categories,name' . ($id ? ",$id" : '')],
            'type' => ['required', 'in:income,expense'],
        ]);
    }

    /**
     * findCategory
     *
     * Busca una categoría o lanza una excepción 404.
     */
    private function findCategory(string $id): Category
    {
        return Category::findOrFail($id);
    }
}
