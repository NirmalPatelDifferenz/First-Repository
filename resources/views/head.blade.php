<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Admin Panel</title>

    <!-- Styles -->
    <link href="{{asset('/public/assets/font-awesome/css/font-awesome.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('/public/assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('/public/assets/css/animate.css')}}">
    <link rel="stylesheet" href="{{asset('/public/assets/css/style.css')}}">

    <!-- Toastr style -->
    <link href="{{ asset('public/assets/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <!-- Gritter -->
    <link href="{{ asset('public/assets/js/plugins/gritter/jquery.gritter.css') }}" rel="stylesheet">
    <!-- Sweet Alert -->
    <link href="{{ asset('public/assets/css/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
</head>
