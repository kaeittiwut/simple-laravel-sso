@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Login') }}</div>

                    <div class="card-body">
                        <div>
                            <h1>Laravel Single Sing On OAuth 2.0 Client</h1>
                            <h3>Running at port 8080</h3>

                            <a class="btn btn-primary btn-block" href="{{ route('oauth.login') }}">Login with OAuth
                                2.0</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
