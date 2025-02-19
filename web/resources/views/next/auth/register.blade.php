{{--
    Register screen (non-API).
    Extends from `next.auth.auth`.
--}}

@extends('next.auth.auth', [
    'title' => __('account_panel.REGISTER')
])

@push('scripts')
    @vite('auth-register.ts')
@endpush

@section('content')
<div id="auth_form_container">
</div>

<a id="auth_login" href="/user--services/login">
    {{ __("account_panel.LOGIN") }}
</a>
@endsection
