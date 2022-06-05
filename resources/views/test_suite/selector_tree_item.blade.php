
@if(isset($editableSuite->id))

    @if($suite->id != $editableSuite->id)

        <option value="{{$suite->id}}"
            @if($suite->id == $editableSuite->parent_id)
                selected
            @endif
        >

            @for ($i = 0; $i < $suite->ancestors()->count(); $i++)
                -
            @endfor

            {{$suite->title}}

        </option>

        @foreach($suite->children as $suite)
            @include('test_suite.selector_tree_item')
        @endforeach
    @endif

@else

    <option value="{{$suite->id}}">

        @for ($i = 0; $i < $suite->ancestors()->count(); $i++)
            -
        @endfor
        {{$suite->title}}
    </option>

    @foreach($suite->children as $suite)
        @include('test_suite.selector_tree_item')
    @endforeach
@endif

