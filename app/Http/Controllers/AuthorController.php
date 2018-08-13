<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthorController extends Controller
{
    
	public function index()
	{
		$authors = \App\Author::get();
    	return view('author.index', compact('authors'));
	}

	public function create()
	{
		$author= new \App\Author();
		return view('author.form', compact('author'));
	}

	public function store(Request $request)
	{
		$author = new \App\Author();
		$author->name = $request->input('name');
		$author->address = $request->input('address');
		$author->save();

		return redirect('author');
	}

	public function edit($id)
	{
		$author = \App\Author::find($id);
		return view('author.form', compact('author'));
	}

	public function update(Request $request, $id)
	{
		$author = \App\Author::find($id);
		$author->name = $request->input('name');
		$author->address = $request->input('address');
		$author->save();

		return redirect('author');
	}

	public function delete($id)
	{
		$author = \App\Author::find($id);
		$author->delete();

		return redirect('author');
	}

    
}
