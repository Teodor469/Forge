<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
        ->with('children')
        ->whereNull('parent_id')
        ->get();

        return response()->json([
            'message' => 'Successfully returned all categories!',
            'categories' => CategoryResource::collection($categories),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCategoryRequest $request)
    {
        try{
            $validated = $request->validated();
    
            $category = Category::create($validated);
    
            return response()->json([
                'message' => 'New category created successfully!',
                'category' => new CategoryResource($category),
            ], 201);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return response()->json([
            'category' => new CategoryResource($category),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        //!The user shouldn't be able to set a parent_id to a Parent category it breaks the nesting
        $this->authorize('update', $category);

        try{
            $validated = $request->validated();

            $category->update($validated);

            return response()->json([
                'message' => 'Category updated successfully!',
                'category' => new CategoryResource($category),
            ], 201);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Category $category)
    {
        $this->authorize('delete', $category);

        try{
            $category->delete();
            return response()->json([
                'message' => 'Successfully deleted a category!'
            ], 200);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
            ], 500);
        }
    }
}
