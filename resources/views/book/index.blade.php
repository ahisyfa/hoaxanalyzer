<a href ="/book/create">Tambah</a>
<br /><br />
<table border="1">
	<thread>
		<tr>
			<th>Title</th>
			<th>Description</th>
			<th>Aksi</th>
		</tr>
	</thread>
	<tbody>
		@foreach($books as $book)
			<tr>
				<td>{{$book->title}}</td>
				<td>{{$book->description}}</td>
				<td>
					<a href="/book/edit/{{$book->id}}">Edit</a>
					<a href="/book/delete/{{$book->id}}">Delete</a>

				</td>
			</tr>
		@endforeach
	</tbody>