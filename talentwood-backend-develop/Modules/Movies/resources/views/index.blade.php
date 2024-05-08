@extends('movies::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('movies.name') !!}</p>
@endsection
