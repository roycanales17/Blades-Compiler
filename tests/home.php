
@template('tests/template.php')
@extends('tests/navbar.php')

<h1>{{ 'Hello World!' }}</h1>
<p>This is a testing content!</p>

<ul>
@foreach([ 'user 1', 'user 2', 'user 3' ] as $test)
	<li>{{ $test }}</li>
@endforeach
</ul>

@extends('tests/footer.php')