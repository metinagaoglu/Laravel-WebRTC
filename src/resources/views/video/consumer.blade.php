@extends('layouts.app')


@section('content')
    <viewer
        stream_id="{{ $streamId }}"
        :auth_user_id="{{ $id }}"
        turn_url="{{ env('TURN_SERVER_URL') }}"
        turn_username="{{ env('TURN_SERVER_USERNAME') }}"
        turn_credential="{{ env('TURN_SERVER_CREDENTIAL') }}"></viewer>
@endsection
