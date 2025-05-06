@extends('home')
@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="row">
       <h2>hello from Dashboard Page</h2>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        console.log('Hello');
    })
</script>
@endsection
