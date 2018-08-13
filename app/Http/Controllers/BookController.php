<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
    	$books = \App\Book::get();
    	return view('book.index', compact('books'));
    }
	
	public function hello(){
		return "Hello Amiin";
	}
}
