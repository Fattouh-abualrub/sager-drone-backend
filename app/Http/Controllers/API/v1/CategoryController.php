<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Validator;
use Exception;

class CategoryController extends Controller
{
    public function index(Request $request)
    {

        try {
            if ($request->action == "all") {
                $categories = Category::select("id","name")->get();
            } else {
                $page = ($request->page) ? $request->page : 1;
                $limit = ($request->limit) ? $request->limit : 10;

                $categories = Category::with(["products"])->limit($limit)->offset(($page - 1) * $limit)->get();
            }


            $total_categories = Category::all()->count();

            return response([
                'categories' => $categories,
                'total' => $total_categories
            ], 200);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {

            $rules = array(
                'name'      => ['required', 'min:3', 'max:255'],
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $category = Category::create([
                'name' => strip_tags($request->name)
            ]);

            $response = [
                'message' =>  "Created Category Successfully",
                'status'  => true,
                'category' => $category
            ];

            return response($response, 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function show(Category $category)
    {
        try {

            if (!$category) {
                return response([
                    'message' => 'Category not found.'
                ], 404);
            } else {

                $category = Category::where(["id" => $category->id])->with(["products"])->get();

                return response([
                    'category' => $category
                ], 200);
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function update(Request $request, Category $category)
    {
        try {

            $rules = array(
                'name'      => ['required', 'min:3', 'max:255'],
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $category->update([
                'name' => strip_tags($request->name)
            ]);


            return response([
                'category' => $category,
                'message' => "Category Updated Successfully.",
                'status' => true
            ], 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function destroy(Category $category)
    {
        try {

            if (!$category) {
                return response([
                    'message' => 'Category not found.'
                ], 404);
            } else {
                if ($category->products->count() > 0) {
                    return response([
                        'message' => 'You cannot delete category.'
                    ], 400);
                } else {
                    $category->delete();

                    return response([
                        'status'  => true,
                        'message' => 'Deleted Category Successfully.'
                    ], 200);
                }
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }
}
