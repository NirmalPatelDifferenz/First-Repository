<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;
use App\Services\Payments\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        return view('product.addProduct');
    }

    public function getProductData(Request $request)
    {
        $columns = ['id', 'product_name', 'product_description', 'product_price', 'product_quantity'];
        $baseQuery = Product::query();
        $recordsTotal = (clone $baseQuery)->count();

        $searchValue = trim((string) $request->input('search.value', ''));
        $filteredQuery = clone $baseQuery;

        if ($searchValue !== '') {
            $filteredQuery->where(function ($query) use ($searchValue) {
                $query->where('product_name', 'like', '%'.$searchValue.'%')
                    ->orWhere('product_description', 'like', '%'.$searchValue.'%');
            });
        }

        $recordsFiltered = (clone $filteredQuery)->count();

        $order = $request->input('order.0', []);
        $columnIndex = isset($order['column']) ? (int) $order['column'] : 0;
        $column = $columns[$columnIndex] ?? 'id';
        $direction = isset($order['dir']) && strtolower($order['dir']) === 'desc' ? 'desc' : 'asc';

        $filteredQuery->orderBy($column, $direction);

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        if ($length < 1) {
            $length = 10;
        }
        $length = min($length, 100);

        $products = $filteredQuery->offset($start)->limit($length)->get();

        $productData = $products->map(function (Product $product) {
            $imageUrl = $product->product_image
                ? Storage::disk('public')->url('images/'.$product->product_image)
                : null;

            return [
                'id' => $product->id,
                'productName' => $product->product_name,
                'productDescription' => $product->product_description,
                'productPrice' => $product->product_price,
                'productQuantity' => $product->product_quantity,
                'productImage' => $imageUrl,
                'action' => '<button class="btn btn-sm btn-danger" value="'.$product->id.'" onClick="deletedProduct('.$product->id.')">Delete</button>',
            ];
        })->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $productData,
        ]);
    }

    public function storeProduct(StoreProductRequest $request, StripeService $stripe)
    {
        $validated = $request->validated();
        $image = $request->file('productImage');
        $imageName = uniqid('product_', true).'.'.$image->getClientOriginalExtension();
        $imagePath = $image->storeAs('images', $imageName, 'public');

        if (!$imagePath) {
            return response()->json([
                'flag' => false,
                'statusCode' => 500,
                'message' => 'The product image could not be stored. Please try again.',
            ], 500);
        }

        $stripeProductId = null;
        $stripePriceId = null;

        try {
            $imageUrl = Storage::disk('public')->url($imagePath);
            $stripeProductId = $stripe->createProduct([
                'name' => $validated['productName'],
                'description' => $validated['productDescription'],
            ], $imageUrl);

            $stripePriceId = $stripe->createPrice(
                $stripeProductId,
                (int) round(((float) $validated['productPrice']) * 100)
            );

            $product = Product::create([
                'product_name' => $validated['productName'],
                'product_price' => $validated['productPrice'],
                'product_quantity' => $validated['productQuantity'],
                'product_description' => $validated['productDescription'],
                'product_image' => $imageName,
                'stripe_product_id' => $stripeProductId,
                'stripe_price_id' => $stripePriceId,
            ]);

            return response()->json([
                'flag' => true,
                'statusCode' => 200,
                'message' => 'Product added successfully',
                'id' => $product->id,
            ]);
        } catch (Throwable $exception) {
            if ($stripePriceId) {
                try {
                    $stripe->deactivatePrice($stripePriceId);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            if ($stripeProductId) {
                try {
                    $stripe->deactivateProduct($stripeProductId);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            Storage::disk('public')->delete($imagePath);
            report($exception);

            return response()->json([
                'flag' => false,
                'statusCode' => 502,
                'message' => 'The product could not be created with the payment provider. Please try again.',
            ], 502);
        }
    }

    public function deleteProduct(DeleteProductRequest $request, StripeService $stripe)
    {
        $product = Product::findOrFail($request->validated()['id']);

        try {
            if ($product->stripe_price_id) {
                $stripe->deactivatePrice($product->stripe_price_id);
            }

            if ($product->stripe_product_id) {
                $stripe->deactivateProduct($product->stripe_product_id);
            }
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'flag' => false,
                'statusCode' => 502,
                'message' => 'The product could not be deactivated in the payment provider. It has not been deleted locally.',
            ], 502);
        }

        $product->delete();

        if ($product->product_image) {
            Storage::disk('public')->delete('images/'.$product->product_image);
        }

        return response()->json([
            'flag' => true,
            'statusCode' => 200,
            'message' => 'Product deleted successfully from local and Stripe',
        ]);
    }
}
