@extends('home')
@section('css')
<style>
    
</style>
@endsection

@section('content')
    <div class="row page-heading">
        <div class="col-lg-10">
            <h2 style="font-weight: 400;">Subscription</h2>
            <ol class="breadcrumb ">
                <li class="breadcrumb-item">
                    <a href="{{route('dashboard')}}">Home</a>
                </li>
                <li class="breadcrumb-item active">
                    <span>Subscription</span>
                </li>
            </ol>
        </div>
    </div>
@endsection

@section('script')
<script>
    console.log('Hello');
</script>
@endsection