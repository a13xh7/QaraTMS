<meta name="user-id" content="{{ auth()->id() }}">

<div id="test_case_block">

    <div class="border rounded py-1 mt-2 mb-2 d-flex justify-content-center">

        <div class="position-static">
            <button type="button"
                class="btn test_run_case_btn {{ $results[$testCase->id] == \App\Enums\TestRunCaseStatus::PASSED ? 'btn-success' : 'btn-outline-success' }}"
                data-status="{{\App\Enums\TestRunCaseStatus::PASSED}}" data-test_run_id="{{$testRun->id}}"
                onclick="updateCaseStatus({{$testRun->id}}, {{$testCase->id}}, {{\App\Enums\TestRunCaseStatus::PASSED}})">
                Passed
            </button>

            <button type="button"
                class="btn test_run_case_btn {{ $results[$testCase->id] == \App\Enums\TestRunCaseStatus::FAILED ? 'btn-danger' : 'btn-outline-danger' }}"
                data-status="{{\App\Enums\TestRunCaseStatus::FAILED}}" data-test_run_id="{{$testRun->id}}"
                onclick="updateCaseStatus({{$testRun->id}}, {{$testCase->id}}, {{\App\Enums\TestRunCaseStatus::FAILED}})">
                Failed
            </button>

            <button type="button"
                class="btn test_run_case_btn {{ $results[$testCase->id] == \App\Enums\TestRunCaseStatus::BLOCKED ? 'btn-warning' : 'btn-outline-warning' }}"
                data-status="{{\App\Enums\TestRunCaseStatus::BLOCKED}}" data-test_run_id="{{$testRun->id}}"
                onclick="updateCaseStatus({{$testRun->id}}, {{$testCase->id}}, {{\App\Enums\TestRunCaseStatus::BLOCKED}})">
                <b>Blocked</b>
            </button>

            <button type="button"
                class="btn test_run_case_btn {{ $results[$testCase->id] == \App\Enums\TestRunCaseStatus::TODO ? 'btn-secondary' : 'btn-outline-secondary' }}"
                data-status="{{\App\Enums\TestRunCaseStatus::TODO}}" data-test_run_id="{{$testRun->id}}"
                onclick="updateCaseStatus({{$testRun->id}}, {{$testCase->id}}, {{\App\Enums\TestRunCaseStatus::TODO}})">
                To Do
            </button>

            <button type="button"
                class="btn test_run_case_btn {{ $results[$testCase->id] == \App\Enums\TestRunCaseStatus::SKIPPED ? 'btn-info' : 'btn-outline-info' }}"
                data-status="{{\App\Enums\TestRunCaseStatus::SKIPPED}}" data-test_run_id="{{$testRun->id}}"
                onclick="updateCaseStatus({{$testRun->id}}, {{$testCase->id}}, {{\App\Enums\TestRunCaseStatus::SKIPPED}})">
                Skipped
            </button>
        </div>

    </div>

    <div id="test_case_content">

        <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">
            <div>
                <span id="status-badge-{{$testCase->id}}"
                    data-test-case-id="{{$testCase->id}}" class="fs-6 badge
                    @switch($results[$testCase->id])
                        @case(\App\Enums\TestRunCaseStatus::PASSED)
                            bg-success
                            @break
                        @case(\App\Enums\TestRunCaseStatus::FAILED)
                            bg-danger
                            @break
                        @case(\App\Enums\TestRunCaseStatus::BLOCKED)
                            bg-warning
                            @break
                        @case(\App\Enums\TestRunCaseStatus::SKIPPED)
                            bg-info
                            @break
                        @default
                            bg-secondary
                    @endswitch
                ">{{$repository->prefix}}-{{$testCase->id}}</span>
                <span class="fs-5">
                    @if($testCase->automated)
                        <i class="bi bi-robot"></i>
                    @else
                        <i class="bi bi-person"></i>
                    @endif
                </span>
                <span class="fs-6">{{$testCase->title}}</span>
            </div>
        </div>

        <div class="p-4 pt-0 position-relative">

            <strong class="fs-5 pb-3">Preconditions</strong>
            <div class="row mb-3 border p-3 rounded">
                <div>
                    {!! $data->preconditions !!}
                </div>
            </div>

            <strong class="fs-6 pb-3">BDD Scenarios</strong>
            <div class="row mt-1 mb-3 border p-2 rounded">
                <div>
                    {!! $data->scenarios !!}
                </div>
            </div>

            @if(isset($comments) && !empty($comments) && count($comments) > 0)
                <strong class="fs-5 pb-3">Comments</strong>
                @foreach($comments->sortByDesc('created_at') as $comment)
                    <div class="row mb-3 border p-3 rounded">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-2 border-bottom pb-2">
                                <div>
                                    <div class="fw-bold">{{ $users->where('id', $comment->user_id)->first()->name }}</div>
                                    <div class="text-muted small">{{ $comment->created_at }}</div>
                                </div>
                            </div>
                            <div class="ps-5">
                                {{$comment->comments}}

                                @php
                                    $attachment = $attachments->where('comment_id', $comment->id)->first();
                                    $url = $attachment->public_url ?? null;
                                    $thumbnailUrl = $attachment->thumbnail_url ?? null;

                                    $isImage = false;
                                    $isVideo = false;
                                    $extension = null;

                                    if ($url) {
                                        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                                        $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'mkv'];
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                                        $isImage = in_array(strtolower($extension), $imageExtensions);
                                        $isVideo = in_array(strtolower($extension), $videoExtensions);
                                    }
                                @endphp

                                @if($url && ($isImage || $isVideo))
                                    <div class="mt-3">
                                        <div class="attachment-trigger"
                                             style="cursor: pointer; display: inline-block; max-width: 100%;"
                                             data-bs-target="#attachmentModal"
                                             data-attachment-id="{{ $attachment->id }}"
                                             data-attachment-url="{{ $url }}"
                                             data-attachment-type="{{ $isImage ? 'image' : 'video' }}"
                                             data-attachment-thumbnail="{{ $thumbnailUrl }}">

                                            @if ($isImage)
                                                <img src="{{ $url }}"
                                                     alt="Attachment Preview" class="img-fluid thumbnail-image"
                                                     style="max-height: 180px; width: auto; display: block;">
                                            @elseif ($isVideo)
                                                <video
                                                    @if ($thumbnailUrl) poster="{{ $thumbnailUrl }}" @endif
                                                    style="max-height: 180px; width: auto; display: block;">
                                                     <source src="{{ $url }}" type="video/{{ $extension }}">
                                                     Your browser does not support the video tag.
                                                 </video>
                                                <small style="display: block; text-align: center;">{{ basename(parse_url($url, PHP_URL_PATH)) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($url)
                                     <div class="mt-3">
                                         <p>File: <a href="{{ $url }}" target="_blank">{{ basename(parse_url($url, PHP_URL_PATH)) }}</a></p>
                                     </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="mb-4">
                <form onsubmit="event.preventDefault(); submitComment({{ $testRun->id }}, {{ $testCase->id }})">
                    @csrf
                    <textarea id="comment" class="form-control" rows="3"></textarea>
                    <input type="file" id="files" multiple class="form-control mt-2">
                    <button type="submit" class="btn btn-primary mt-2">Submit Comment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="attachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalLabel">Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="modalAttachmentContent">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset_path('js/test_run.js') }}"></script>
@endpush
