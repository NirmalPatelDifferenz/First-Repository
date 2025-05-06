@extends('home')
@section('css')
<style>
    .content-bar{
        display: flex;
        justify-content: end;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Product</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('dashboard')}}">Home</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Product</strong>
            </li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-lg-12" style="padding: 0px; margin-top: 15px;">
        <div class="ibox">
            <div class="ibox-title">
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="content-bar">
                    <button type="button" class="btn btn-primary addProductBtn" data-toggle="modal" data-target="#addProductModel">Add Product</button>
                </div>
                {{-- Product Table --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-example" id="productTable">
                        <thead>
                            <tr>
                                <th scope="col" style="display: none;">id</th>
                                <th scope="col">ProductName</th>
                                <th scope="col">productDescription</th>
                                <th scope="col">productPrice</th>
                                <th scope="col">productQuantity</th>
                                <th scope="col">productImage</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Product Model --}}
<div class="modal inmodal fade" id="addProductModel" tabindex="-1" role="dialog"  aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Add Product</h4>
            </div>
            <form action="" method="POST" id="addProductForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="url" value="{{route('product.store')}}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="productName">Enter Product Name</label>
                            <input type="text" name="productName" id="productName" class="form-control" placeholder="Enter Product Name">
                        </div>
                        <div class='col-md-6 form-group'>
                            <label for="productPrice">Enter Product Price</label>
                            <input type="text" name="productPrice" id="productPrice" class="form-control" placeholder="Enter Product Price">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="productDescription">Enter Product Description</label>
                            <textarea name="productDescription" id="productDescription" class="form-control" placeholder="Enter Product Description" rows="5" style="resize: none"></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="productQuantity">Enter Product Quantity</label>
                            <input type="text" name="productQuantity" id="productQuantity" class="form-control" placeholder="Enter Product Quantity">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="productImage">Upload Product Image</label>
                            <input type="file" name="productImage" id="productImage" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.validator.addMethod("extension", function (value, element, param) {
            param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
            return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
        }, "Please enter a file with a valid extension.");

        $('#addProductForm').validate({
            rules:{
                productName:{
                    required:true,
                },
                productPrice:{
                    required:true,
                    number:true,
                },
                productDescription:{
                    required:true,
                },
                productQuantity:{
                    required:true,
                    number:true,
                },
                productImage:{
                    required:true,
                    extension:"jpg,jpeg,png",
                },
            },messages:{
                productName:{
                    required:"Please enter product name.",
                },
                productPrice:{
                    required:"Please enter product price.",
                    number:"Please enter a valid price",
                },
                productDescription:{
                    required:"Please enter product description.",
                },
                productQuantity:{
                    required:"Please enter product quantity.",
                    number:"Please enter a valid quantity",
                },
                productImage:{
                    required:"Please upload product image.",
                    extension:"Please upload a valid image file (jpg, jpeg, png).",
                },
            },
            submitHandler:function(e){
                var formData = new FormData($('#addProductForm')[0]);
                var url = $('#addProductForm').find('input[name=url]').val();
                $.ajax({
                    url:url,
                    type:'POST',
                    data:formData,
                    contentType:false,
                    processData:false,
                    dataType:'json',
                    success:function(response){
                        if(response.statusCode == 200 && response.flag == true){
                            swal({
                                title: "Success!",
                                type: "success",
                                text: response.message,
                                icon: "success",
                            },function(){
                                $('#addProductModel').modal('hide');
                                $('#addProductForm')[0].reset();
                                $('#productTable').DataTable().ajax.reload();
                            });
                        }else{
                            toastr.error(response.message);
                        }
                    }
                })
            }
        });

        var table = "";
        var url = "{{route('product.getData')}}";
        table = $('#productTable').dataTable({
            processing:true,
            serverSide:true,
            responsive:true,
            autoWidth:false,
            pageLength:10,
            lengthMenu:[[10, 25, 50, -1], [10, 25, 50, "All"]],
            searching:true,
            paging:false,
            ajax:{
                url:url,
                dataSrc:'data',
            },
            columns:[
                {data:'id', name:'id' , visible:false},
                {data:'productName', name:'productName'},
                {data:'productDescription', name:'productDescription'},
                {data:'productPrice', name:'productPrice',render :function(data){
                    return "$"+ data;
                }},
                {data:'productQuantity', name:'productQuantity'},
                {data: 'productImage', name: 'productImage', render: function(data) {
                    return "<img src='"+data+"' width='50' height='50' />";
                }},
                {data:'action', name:'Action'},
            ],
            order:[[0, 'asc']],
        });
    });

    function deletedProduct($id){
        var url = "{{route('product.delete', ':id')}}";
        url = url.replace(':id', $id);
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this imaginary file!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        },function(isConfirm){
            if(isConfirm){
                $.ajax({
                    url:url,
                    type:'POST',
                    dataType:'json',
                    data:{
                        id:$id,
                    },
                    success:function(response){
                        if(response.statusCode == 200 && response.flag == true){
                            swal("Deleted!", response.message, "success");
                            $('#productTable').DataTable().ajax.reload();
                        }else{
                            toastr.error(response.message);
                        }
                    }
                });
            }else{
                swal("Cancelled", "Your imaginary file is safe :)", "error");
            }
        });
    }
</script>
@endsection
