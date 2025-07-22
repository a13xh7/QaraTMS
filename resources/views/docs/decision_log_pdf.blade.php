<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $log->title }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.5; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        h1 { font-size: 24px; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .meta-info { margin-bottom: 20px; }
        .meta-info p { margin: 0; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 18px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .tags span { display: inline-block; background-color: #eee; padding: 5px 10px; border-radius: 3px; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $log->title }}</h1>
        
        <div class="meta-info">
            <p><strong>Status:</strong> {{ $log->status }}</p>
            <p><strong>Decision Date:</strong> {{ $log->decision_date ? $log->decision_date->format('d M Y') : '—' }}</p>
            <p><strong>Decision Owner:</strong> {{ $log->decision_owner ?? '—' }}</p>
            <p><strong>Involved QA:</strong> {{ $log->involved_qa ?? '—' }}</p>
            <p><strong>Sprint/Release:</strong> {{ $log->sprint_release ?? '—' }}</p>
        </div>

        <div class="section">
            <h2>Context</h2>
            <p>{{ $log->context ?? '—' }}</p>
        </div>

        <div class="section">
            <h2>Decision</h2>
            <p>{{ $log->decision ?? '—' }}</p>
        </div>

        <div class="section">
            <h2>Impact / Risk</h2>
            <p>{{ $log->impact_risk ?? '—' }}</p>
        </div>

        @if(!empty($log->tags))
        <div class="section tags">
            <h2>Tags</h2>
            @foreach($log->tags as $tag)
                <span>{{ $tag }}</span>
            @endforeach
        </div>
        @endif
    </div>
</body>
</html> 