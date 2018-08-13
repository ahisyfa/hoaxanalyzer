@extends('layouts.layout')

@section('content')

	<form method="POST" action="">
		{{csrf_field() }}
		<table>
			<tr>
				<td>Nama</td>
				<td>
					<input type="text" name="name" value="{{$author->name}}" />
				</td>
			</tr>
			<tr>
				<td>Alamat</td>
				<td>
					<textarea name="address">{{$author->address}}</textarea>
				</td>
			</tr>
			<tr>
				<td>
					<input type="submit" name="submit">
				</td>
				<td></td>
			</tr>
		</table>
	</form>
	
@endsection