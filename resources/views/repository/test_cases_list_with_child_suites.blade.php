@php use App\Suite;use App\TestCase;
/**
 * @var TestCase[] $testCases
 * @var Suite $suite
 */
@endphp
@if($suite->descendants()->count() > 0)

    {{--    PARENT SUITE TEST CASES   --}}
    @foreach($testCases as $testCase)
        @include('repository.test_case_list_item')
    @endforeach

    <br>

    @php /** @var Suite $childSuite */ @endphp
    @foreach($suite->descendants()->get() as $childSuite)

        {{--   SHOW CHILD SUITE TITLE WITH FULL PATH --}}
        @if($childSuite->testCases()->count() > 0)
            <div style="background: #7c879138; padding-left: 5px; padding-bottom: 5px;">

                <i class="bi bi-folder2 fs-5"></i>

                <span class="text-muted" style="font-size: 14px">
                    @php /** @var Suite $parent */ @endphp
                    @foreach($childSuite->ancestors()->get()->reverse() as $parent)
                        {{$parent->title}}
                        <i class="bi bi-arrow-right-short"></i>
                    @endforeach
                </span>
                <b>{{$childSuite->title}}</b>
            </div>
        @endif

        {{--    CHILD SUITE TEST CASES   --}}
        @foreach(TestCase::where('suite_id', $childSuite->id)->orderBy('order')->get() as $testCase)
            @include('repository.test_case_list_item')
        @endforeach

    @endforeach

@else

    @foreach($testCases as $testCase)
        @include('repository.test_case_list_item')
    @endforeach

@endif


