<form method="POST" action="">
	{{ csrf_field() }}
	<input type="text" name="title" value="{{ $book->title }}">
	<input type="text" name="descriptions" value="{{ $book->descriptions }}"/>
	 <select name="author_id">
	 	@foreach($authors as $author)
		  <option value="{{$author->id}}"> {{$author->name}} </option>
		@endforeach
	</select> 
	<input type="submit" name="submit">
</form>