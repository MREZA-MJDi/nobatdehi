@extends('layouts.salon')

@section('title', 'ویرایش پست')

@section('content')

    @include(
        'salon.posts._form',
        [
            'mode' => 'edit',
            'post' => $post,
        ]
    )

@endsection
