<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Decision Logs - Bulk Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        
        .log-card {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            page-break-inside: avoid;
        }
        
        .log-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .log-title {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            margin: 0 0 5px 0;
        }
        
        .log-meta {
            font-size: 11px;
            color: #666;
        }
        
        .log-meta span {
            margin-right: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-badge.draft {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.finalized {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .section-content {
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
        }
        
        .tags {
            margin-top: 10px;
        }
        
        .tag {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-right: 5px;
            margin-bottom: 3px;
        }
        
        .artifacts {
            margin-top: 10px;
            font-size: 10px;
            color: #666;
        }
        
        .artifact-item {
            margin-bottom: 3px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Decision Logs - Bulk Export</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>Total Records: {{ $logs->count() }}</p>
    </div>

    @foreach($logs as $index => $log)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="log-card">
            <div class="log-header">
                <div class="log-title">{{ $log->title }}</div>
                <div class="log-meta">
                    <span><strong>Type:</strong> {{ $log->decision_type ?? '—' }}</span>
                    <span><strong>Owner:</strong> {{ $log->decision_owner ?? '—' }}</span>
                    <span><strong>Date:</strong> {{ $log->decision_date ? $log->decision_date->format('M j, Y') : '—' }}</span>
                    <span><strong>Sprint/Release:</strong> {{ $log->sprint_release ?? '—' }}</span>
                    <span class="status-badge {{ strtolower($log->status) }}">{{ $log->status }}</span>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Context</div>
                <div class="section-content">{{ $log->context ?? 'No context provided.' }}</div>
            </div>

            <div class="section">
                <div class="section-title">Decision</div>
                <div class="section-content">{{ $log->decision ?? 'No decision recorded.' }}</div>
            </div>

            <div class="section">
                <div class="section-title">Impact / Risk</div>
                <div class="section-content">{{ $log->impact_risk ?? 'No impact/risk information provided.' }}</div>
            </div>

            @if($log->tags && count($log->tags) > 0)
                <div class="tags">
                    <div class="section-title">Tags</div>
                    @foreach($log->tags as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            @if($log->related_artifacts && count($log->related_artifacts) > 0)
                @php
                    $validArtifacts = array_filter($log->related_artifacts, function($attachment) {
                        return ($attachment['size'] ?? 0) > 0;
                    });
                @endphp
                @if(count($validArtifacts) > 0)
                    <div class="artifacts">
                        <div class="section-title">Related Artifacts</div>
                        @foreach($validArtifacts as $attachment)
                            <div class="artifact-item">
                                • {{ $attachment['name'] }} ({{ number_format($attachment['size'] / 1024, 1) }} KB)
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="footer">
                <small>Decision Log ID: {{ $log->id }} | Last Updated: {{ $log->updated_at->format('M j, Y g:i A') }}</small>
            </div>
        </div>
    @endforeach
</body>
</html> 