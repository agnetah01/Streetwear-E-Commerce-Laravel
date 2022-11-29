<?php

namespace App\Http\Livewire\Admin\Product;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    
    public $categories;

    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $price;
    public $quantity;
    public $status;
    public $images = [];

    public $meta_title;
    public $meta_keyword;
    public $meta_description;

    public function mount(){
        $this->categories = Category::all();
    }


    protected $rules = [
        "categoryId"       => ['required','integer'], 
        "name"             => ['required','string'],
        "slug"             => ['required','string','max:255'],
        "description"      => ['required','string'],
        "price"            => ['required','integer','min:0'],
        "quantity"         => ['required','integer','min:0'],
        "status"           => ['required','in:Active,Draft'],
        "images.*"         => ['required','image','mimes:jpg,jpeg,png,webp'],
    
        "meta_title"       => ['nullable','string','max:255'],
        "meta_keyword"     => ['nullable','string'],
        "meta_description" => ['nullable','string']
    ];


    public function submit()
    {   
        $validatedData = $this->validate();

        $category = Category::findOrFail($this->categoryId);

        $product = $category->products()->create($validatedData);

        if(count($this->images)){
            foreach ($this->images as $image) {
                $path = $image->store('products');
                
                $product->productImages()->create(['image' => $path]);
            }
        }
        
        return redirect()->route('admin.product.index')->with('success','The product has been created successfully');
    }


    public function render()
    {
        return view('livewire.admin.product.create');
    }
}