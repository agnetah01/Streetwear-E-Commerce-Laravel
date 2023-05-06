<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Arr;

class ProductShow extends Component
{
    public $productId;

    public $userOptions;

    public $optionValuesArray = [];
    public $optionMatrix = [];

    public $productSku;
    public $quantity ;
    public $price ;
    
    public function dehydrate(){
        $this->dispatchBrowserEvent('contentChanged');
    }

    public function mount(){
        $product = Product::findOrFail($this->productId);
        if($product->options && $product->options->count()){
            foreach ($product->options as $option ) {
                $this->userOptions[$option->name] = $option->optionValues[0]->id;
            }   
            
            foreach ($product->options as $option) {
                array_push($this->optionValuesArray,$option->optionValues->pluck('id')->toArray());
            }
            
            $this->optionMatrix = Arr::crossJoin(...$this->optionValuesArray);
            
            //the idea here is that i want to get the sku_id of the the use selected option
            //get all product sku values grouped by the sku_id
            $productAllSkus = $product->productSkusValues->groupBy('product_sku_id');
            
            //get the right columns that has the same sku_id and then get it's sku 
            $this->productSku = $productAllSkus->first(function ($values, $keys){
                $res = $values->whereIn('option_value_id',$this->optionMatrix[0]);
                return $res->count() ===  $values->count();
            })->first()->productSku;

            $this->quantity =  $this->productSku->quantity ; 
            $this->price =  number_format($this->productSku->price,2,'.','');
        }else{

            $this->quantity =  $product->productSkus->first()->quantity ; 
            $this->price =  number_format($product->productSkus->first()->price,2,'.','');
        }
           
    }

    //trigger when the userOptions is updated
    public function updatedUserOptions(){
        $product = Product::findOrFail($this->productId);
        [$key,$option_value_ids] = Arr::divide($this->userOptions);
            
        //the idea here is that i want to get the sku_id of the the use selected option
        //get all product sku values grouped by the sku_id
        $productAllSkus = $product->productSkusValues->groupBy('product_sku_id');
        
        //get the right columns that has the same sku_id and then get it's sku 
        $this->productSku = $productAllSkus->first(function ($values, $keys) use($option_value_ids){
            $res = $values->whereIn('option_value_id',$option_value_ids);
            return $res->count() ===  $values->count();
        })->first()->productSku;

        $this->quantity = $product->productSkus->count() > 0 ? $this->productSku->quantity : $product->quantity;
        $this->price = $product->productSkus->count() > 0 ? number_format($this->productSku->price,2,'.','') : number_format($product->price,2,'.','');

    }

    public function addToCart(){
        //key is the option name and the value is the option value
        if(auth()->id()){
            if($this->productSku->quantity >= $this->quantity && $this->productSku->quantity != 0){
                $product = Product::findOrFail($this->productId);
                $product->carts()->create([
                    'user_id' => auth()->user()->id,
                    'product_sku_id' => $this->productSku->id,
                    'price' => $this->productSku->price,
                ]);
    
                session()->flash('success','Product added to cart successfully');
                return redirect()->route('cart');
            }

        }else{
            return redirect()->route('login');
        }
    }

    public function buyNow(){
        dd($this->userOptions,$this->quantity);
    }

    public function render()
    {
        return view('livewire.product-show',[
            'product' => Product::findOrFail($this->productId)
        ]);
    }
}
