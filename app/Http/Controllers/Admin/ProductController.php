<?php

namespace App\Http\Controllers\Admin;

use App\Models\Mesin;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function index(){

        $products = Product::with('category','mesin')->get();
        return view ('pages.products.index', [
            "products" => $products,
        ]);
    }

    public function create(){
        $categories = Category::all();
        $mesin = Mesin::all();

        return view ('pages.products.create',[
        "categories" => $categories,    
        "mesin" => $mesin,]  
        );
    }

    public function store(Request $request){
    $validated = $request->validate([
    "name" => "required|min:3",
    "description" => "nullable",
    "mesin_id" => "required",
    "stock"=> "required",
    "category_id" => "required",
    "sku" => "required",  
    ]);
    
    Product::create($validated);
        return redirect('/products');
    }

    public function edit($id)
    {
        $categories = Category::all();
        $mesin = Mesin::all();
        $product = Product::findOrFail($id);
        
        return view ('pages.products.edit',[
        "categories" => $categories,
        "mesin" => $mesin,
        "product" => $product,    
        ]);
    }


    public function update(Request $request, $id){
        $validated = $request->validate([
        "name" => "required|min:3",
        "description" => "nullable",
        "mesin_id" => "required",
        "stock"=> "required",
        "category_id" => "required",
        "sku" => "required",  
    
    ]);
        Product::where('id', $id)->update($validated);
   
            return redirect('/products');
        }

    public function delete($id)
    {
        $products = Product::where('id', $id);
        $products->delete();

        return redirect('/products');
    }

    

    
}
