@extends('layouts.login_master')

@section('page_title', 'Complete Registration')

@section('content')
<div class="page-content login-cover">
    <div class="content-wrapper">
        <div class="content d-flex justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-center text-info mb-3">Complete Your Registration</h5>
                        {{-- Error Alert Area --}}
                        @if($errors->any())
                        <div class="alert alert-danger border alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    
                            @foreach($errors->all() as $er)
                            <span><i class="material-symbols-rounded pb-2px">hdr_strong</i> {{ $er }}</span> <br>
                            @endforeach
                    
                        </div>
                        @endif
                        <form method="post" enctype="multipart/form-data" class="wizard-form steps-validation" action="{{ route('register.submit', $invitation->token) }}" data-fouc>
                            @csrf
                            @include('partials.user_fields', ['mode' => 'invitation'])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
