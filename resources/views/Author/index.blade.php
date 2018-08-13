@extends('layouts.layout')

@section('content')

	<a href ="/author/create">Tambah</a>
	<br /><br />
	<table border="1">
		<thread>
			<tr>
				<th>Nama</th>
				<th>Alamat</th>
				<th>Aksi</th>
			</tr>
		</thread>
		<tbody>
			@foreach($authors as $author)
				<tr>
					<td>{{$author->name}}</td>
					<td>{{$author->address}}</td>
					<td>
						<a href="/author/edit/{{$author->id}}">Edit</a>
						<a href="/author/delete/{{$author->id}}">Delete</a>

					</td>
				</tr>
			@endforeach
		</tbody>
	</table>

@endsection