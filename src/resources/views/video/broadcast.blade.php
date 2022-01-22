@extends('layouts.app')


@section('content')
    <broadcaster :auth_user_id="{{ $id }}" env="{{ env('APP_ENV') }}"
                 turn_url="{{ env('TURN_SERVER_URL') }}" turn_username="{{ env('TURN_SERVER_USERNAME') }}"
                 turn_credential="{{ env('TURN_SERVER_CREDENTIAL') }}" />
@endsection
