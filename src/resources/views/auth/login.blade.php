@extends('auth')
@section('title', 'Sign in to your account')

@section('content')
    <h1 class="h4 text-white mb-4">Sign in to your account</h1>

    <form class="m-t-md"
          role="form"
          method="POST"
          data-controller="form"
          data-form-need-prevents-form-abandonment-value="false"
          data-action="form#submit"
          action="{{ route('login.auth') }}">
        @csrf

        @includeWhen($isLockUser,'auth.lockme')
        @includeWhen(!$isLockUser,'auth.signin')
    </form>
@endsection
