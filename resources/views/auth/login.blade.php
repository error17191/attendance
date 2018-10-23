@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" aria-label="{{ __('Login') }}"
                          @submit.prevent="login"
                          novalidate
                    >
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-sm-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email"
                                       @input="errors.email = null"
                                       class="form-control"
                                       name="email"
                                       v-model="email"
                                       :class="{'is-invalid': errors.email}"
                                       required autofocus>
                                <span v-if="errors.email" class="invalid-feedback">@{{errors.email}}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control"
                                       name="password"
                                       @input="errors.password = null"
                                       required
                                       v-model="password"
                                       :class="{'is-invalid': errors.password}"
                                >
                                <span v-if="errors.password" class="invalid-feedback">@{{errors.password}}</span>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @lang('Login')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
