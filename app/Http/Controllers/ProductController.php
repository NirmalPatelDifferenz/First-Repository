<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Stripe\Stripe;
use Stripe\Product as StripeProduct;
use Stripe\Price as StripePrice;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index()
    {
        return view('product.addProduct');
    }

    public function getProductData()
    {
        $productData = [];
        $data = Product::whereNull('deleted_at')->get();
        foreach ($data as $item){
            $productData[] = [
                'id' => $item->id,
                'productName' => $item->product_name,
                'productDescription' => $item->product_description,
                'productPrice' => $item->product_price,
                'productQuantity' => $item->product_quantity,
                'productImage' => asset('public/storage/images/' . $item->product_image),
                'action' => '<button class="btn btn-sm btn-danger" value="'.$item->id.'" onClick=deletedProduct("'.$item->id.'")>Delete</button>',
            ];
        }
        return response()->json(['data' => $productData]);
    }

    // Add Product Stripe with stripe product creation and price creation
    public function storeProduct(Request $request)
    {
        $productName = $request->productName;
        $productPrice = $request->productPrice;
        $productQuantity = $request->productQuantity;
        $productDescription = $request->productDescription;
        $productImage = $request->file('productImage');
        $productImageName = time() . '.' . $productImage->getClientOriginalExtension();
        $productImage->storeAs('public/images', $productImageName);

        $stripeSecret = env('STRIPE_SECRET_KEY');
        $productResponse = Http::asForm()->withToken($stripeSecret)
            ->post('https://api.stripe.com/v1/products', [
                'name' => $productName,
                'description' => $productDescription,
                'images[]' => $productImage,
            ]);
        $stripeProductId = $productResponse->json('id');
        $priceResponse = Http::asForm()->withToken($stripeSecret)
            ->post('https://api.stripe.com/v1/prices', [
                'unit_amount' => $productPrice * 100,
                'currency' => 'usd',
                'product' => $stripeProductId,
            ]);
        $data = [
            'product_name' => $productName,
            'product_price' => $productPrice,
            'product_quantity' => $productQuantity,
            'product_description' => $productDescription,
            'product_image' => $productImageName,
            'stripe_product_id' => $stripeProductId,
            'stripe_price_id' => $priceResponse->json('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $productData = Product::create($data);
        if ($productData) {
            return json_encode(array('flag' => true,'statusCode'=>200, 'message' => 'Product added successfully'));
        } else {
            return json_encode(array('status' => false , 'statusCode' => 400, 'message' => 'Something went wrong'));
        }
    }

    // Delete Product in localDB With Stripe deactivation product and price
    public function deleteProduct(Request $request)
    {
        $productId = $request->id;
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status' => false,
                'statusCode' => 400,
                'message' => 'Product not found'
            ]);
        }
        $stripeSecret = env('STRIPE_SECRET_KEY');
        if (!empty($product->stripe_price_id)) {
            $deactivatePrice = Http::asForm()->withToken($stripeSecret)
                ->asForm()
                ->post("https://api.stripe.com/v1/prices/{$product->stripe_price_id}", [
                    'active' => 'false',
                ]);
            info("Stripe Price Deactivate Response: " . $deactivatePrice->body());
        }
        if (!empty($product->stripe_product_id)) {
            $archiveProduct = Http::withToken($stripeSecret)
                ->asForm()
                ->post("https://api.stripe.com/v1/products/{$product->stripe_product_id}", [
                    'active' => 'false',
                ]);
            info("Stripe Product Archive Response: " . $archiveProduct->body());
        }
        $imagePath = public_path('storage/images/' . $product->product_image);
        if (file_exists($imagePath))
        {
            unlink($imagePath);
        }
        $product->update(['deleted_at' => now()]);
        return response()->json([
            'flag' => true,
            'statusCode' => 200,
            'message' => 'Product deleted successfully from local and Stripe'
        ]);
    }
}
