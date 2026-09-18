@extends('layouts.salon')

@section('title', 'افزودن پست')

@section('content')

    @include(
        'salon.posts._form',
        [
            'mode' => 'create',
            'post' => null,
        ]
    )

@endsection
