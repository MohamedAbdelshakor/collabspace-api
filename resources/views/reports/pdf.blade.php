<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->type->label() }} - Collabspace</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 8px;
            color: #0f172a;
        }
        .meta {
            color: #64748b;
            font-size: 11px;
            margin-top: 4px;
        }
        .ai-summary {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 12px 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .ai-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        tr:nth-child(even) td {
            background-color: #fbfcfe;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef9c3; color: #a16207; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .footer {
            margin-top: 32px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Collabspace</div>
        <div class="report-title">{{ $report->type->label() }}</div>
        <div class="meta">
            Generated on: {{ now()->toFormattedDateString() }} | 
            Period: {{ $report->start_date?->format('Y-m-d') }} to {{ $report->end_date?->format('Y-m-d') }} |
            Requested by: {{ $report->user?->name ?? 'System' }}
        </div>
        @if ($report->note)
            <div class="meta" style="margin-top: 6px; font-style: italic;">
                Note: {{ $report->note }}
            </div>
        @endif
    </div>

    @if ($report->ai_summary)
        <div class="ai-summary">
            <div class="ai-title">🤖 AI Executive Summary & Insights</div>
            <div>{{ $report->ai_summary }}</div>
        </div>
    @endif

    @if (!empty($items))
        <table>
            <thead>
                <tr>
                    @foreach (array_keys($items[0] ?? []) as $col)
                        <th>{{ ucwords(str_replace('_', ' ', (string) $col)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $row)
                    <tr>
                        @foreach ($row as $val)
                            <td>{{ is_array($val) ? json_encode($val) : $val }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #64748b; font-style: italic;">No records found for this period.</p>
    @endif

    <div class="footer">
        Collabspace Project Management Dashboard · Confidential & Proprietary · Page 1 of 1
    </div>
</body>
</html>
