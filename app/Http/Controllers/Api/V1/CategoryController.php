<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoryController
 *
 * Controlador para gestionar las categorías de ingreso o gasto del usuario.
 * Proporciona métodos CRUD para operar sobre el modelo Category.
 */
class CategoryController extends Controller
{
    /**
     * Muestra todas las categorías existentes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(Category::all());
    }

    /**
     * Crea una nueva categoría en la base de datos.
     *
     * @param  Request  $request  Datos validados: nombre único y tipo (income o expense)
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);

        $category = Category::create($validated);

        return response()->json($category, Response::HTTP_CREATED);
    }

    /**
     * Muestra los detalles de una categoría específica.
     *
     * @param  string  $id  ID de la categoría
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        return response()->json($this->findCategory($id));
    }

    /**
     * Actualiza los datos de una categoría existente.
     *
     * @param  Request  $request  Datos opcionales validados: nombre único y tipo
     * @param  string   $id       ID de la categoría a actualizar
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $category = $this->findCategory($id);

        $validated = $this->validateCategory($request, $id);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Elimina una categoría de la base de datos.
     *
     * @param  string  $id  ID de la categoría
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $category = $this->findCategory($id);
        $category->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Valida los datos del request para crear o actualizar una categoría.
     *
     * @param  Request     $request   Objeto con los datos enviados por el cliente
     * @param  string|null $id        ID de la categoría si es una actualización
     * @return array                   Datos validados
     */
    private function validateCategory(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'name' => ['string', 'required', 'unique:categories,name' . ($id ? ",$id" : '')],
            'type' => ['required', 'in:income,expense'],
        ]);
    }

    /**
     * Busca una categoría por ID o lanza una excepción 404 si no existe.
     *
     * @param  string  $id  ID de la categoría
     * @return Category     Modelo de la categoría encontrada
     */
    private function findCategory(string $id): Category
    {
        return Category::findOrFail($id);
    }
}