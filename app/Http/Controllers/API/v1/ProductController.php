<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Media;
use App\Models\CategoryProduct;
use Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        try {

            $page = ($request->page) ? $request->page : 1;
            $limit = ($request->limit) ? $request->limit : 10;

            $products = Product::with(["user"])->limit($limit)->offset(($page - 1) * $limit)->get();

            $total_products = Product::all()->count();

            foreach ($products as &$prod) {
                $categories = [];
                foreach ($prod->categories as $cat) {
                    $categories[] = $cat->name;
                }

                $prod->categories_name = $categories;
            }

            $response = ['products' => $products, 'total' => $total_products];

            return response($response, 200);
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
                'name'             => ['required', 'min:3', 'max:255'],
                'description'      => ['nullable', 'min:3', 'max:1000'],
                'price'            => ['required', 'numeric', 'min:1', 'max:1000'],
                'quantity'         => ['required', 'integer', 'min:1'],
                "categories"       => ['required', 'array', 'min:1'],
                "categories.*"     => ['required', 'distinct'],
                "image"            => ['mimes:jpeg,jpg,png,gif', 'required', 'max:10000']

            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'user_id' => Auth::guard('sanctum')->user()->id,
            ]);

            foreach ($request->categories as $category) {
                CategoryProduct::create([
                    'product_id'    => $product->id,
                    'category_id'   => $category["id"]
                ]);
            }

            if ($request->hasFile('image')) {
                $image = $product->addMedia($request->image)->toMediaCollection('products');
            }

            return response(["product" => $product, "status" => true, "message" => "Product Created Successfully"], 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function show(Product $product)
    {
        try {

            if (!$product) {
                return response([
                    'message' => 'Product not found.'
                ], 404);
            } else {

                $product = Product::where(['id' => $product->id])->with(["categories"])->first();
                return response([
                    'status'  => true,
                    'product' => $product
                ], 200);
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            $rules = array(
                'name'             => ['required', 'min:3', 'max:255'],
                'description'      => ['nullable', 'min:3', 'max:1000'],
                'price'            => ['required', 'numeric', 'min:1', 'max:1000'],
                'quantity'         => ['required', 'integer', 'min:1'],
                "categories"       => ['required', 'array', 'min:1'],
                "categories.*"     => ['required', 'distinct'],
            );

            if ($request->hasFile('image')) {
                $rules['image'] = 'mimes:jpeg,jpg,png,gif|required|max:10000';
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'quantity' => $request->quantity
            ]);

            CategoryProduct::where(['product_id' => $product->id])->delete();

            foreach ($request->categories as $category) {

                CategoryProduct::create([
                    'product_id'    => $product->id,
                    'category_id'   => $category["id"]
                ]);
            }
            if ($request->hasFile('image')) {

                $image = $product->addMedia($request->image)->toMediaCollection('products');
                if($image){
                    Media::whereNotIn('id', [$image->id])->delete();
                }

            }

            return response(["product" => $product, "status" => true,  "message" => "Product Upddated Successfully"], 201);

        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {

            if (!$product) {
                return response([
                    'message' => 'Product not found.'
                ], 404);
            } else {
                $product->delete();

                return response([
                    'status'  => true,
                    'message' => 'Deleted Product Successfully.'
                ], 200);
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }
}
