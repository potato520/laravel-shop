@extends('master')

@section('title', '类别')

@section('content')
    <!--一级类别-->
    <div class="weui_cells_title">选择书籍类别</div>
    <div class="weui_cells weui_cells_split">
        <div class="weui_cell weui_cell_select">
            <div class="weui_cell_bd weui_cell_primary">
                <select class="weui_select" name="category">
                    @foreach($categorys as $category)
                        <option value="{{$category->id}}">{{$category->name}}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <!--二级类别-->
    <div class="weui_cells weui_cells_access">
        <a class="weui_cell" href="javascript:;">
            <div class="weui_cell_bd weui_cell_primary">
                <p>cell standard</p>
            </div>
            <div class="weui_cell_ft">说明文字</div>
        </a>
        <a class="weui_cell" href="javascript:;">
            <div class="weui_cell_bd weui_cell_primary">
                <p>cell standard</p>
            </div>
            <div class="weui_cell_ft">说明文字</div>
        </a>
    </div>

@endsection

@section('my-js')
    <script type="text/javascript">
        // 第一次进来获取 value值
        _getCategory();

        // 监听 select 如果发生改变获取 value的值
        $('.weui_select').change(function (event) {
            var parent_id = $('.weui_select option:selected').val();
            _getCategory();
        });

        // 第一次进入当前页面获取默认的 select 的value
        function _getCategory() {
            console.log('获取类别数据');
            var parent_id = $('.weui_select option:selected').val();
            $.ajax({
                type: "POST",
                url: '{{ url('service/category/parent_id') }}',
                dataType: 'json',
                data:{ parent_id:parent_id, _token: "{{csrf_token()}}" },
                cache: false,
                success: function(data) {
                    if(data == null) {
                        $('.bk_toptips').show();
                        $('.bk_toptips span').html('服务端错误');
                        setTimeout(function() {$('.bk_toptips').hide();}, 2000);
                        return;
                    }
                    if(data.status != 0) {
                        $('.bk_toptips').show();
                        $('.bk_toptips span').html(data.message);
                        setTimeout(function() {$('.bk_toptips').hide();}, 2000);
                        return;
                    }

                    console.log(data);
                    // 获取到子分类
                    $('.weui_cells_access').html('');
                    for (var i=0; i<data.categorys.length; i++){
                        // 分类的链接
                        var next = '{{ url('/product/category_id/') }}' +'/'+ data.categorys[i].id;
                        // 生成子分类
                        var node = '<a class="weui_cell" href="'+ next +'">'+
                            '<div class="weui_cell_bd weui_cell_primary">'+
                                '<p>'+ data.categorys[i].name +'</p>'+
                            '</div>'+
                            '<div class="weui_cell_ft">说明文字</div>'+
                            '</a>';
                        $('.weui_cells_access').append(node);
                    }

                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        }
    </script>

@endsection