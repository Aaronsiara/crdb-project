@extends('layouts.app')

@section('title', 'Segmentation Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Segmentation Results</h1>
    <a href="{{ route('segmentation.create') }}" class="btn btn-crdb">New Segmentation</a>
</div>

@if ($run->isFailed())
    <div class="alert alert-danger">
        <strong>This run failed.</strong><br>
        {{ $run->error_message }}
    </div>
@endif

<div class="row mb-1">
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">File</div>
            <div class="fw-bold" style="font-size:14px;">{{ $run->original_filename }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Total Records</div>
            <div class="fs-4 fw-bold text-primary">{{ $totalRecords }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Segments Found</div>
            <div class="fs-4 fw-bold text-primary">{{ $profiles ? count($profiles['rows']) : '—' }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Status</div>
            @php
                $badge = ['complete' => 'success', 'running' => 'info', 'pending' => 'secondary', 'failed' => 'danger'][$run->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $badge }} fs-6">{{ ucfirst($run->status) }}</span>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Segment Profiles</div>
    <div class="card-body">
        @if ($profiles && count($profiles['rows']) > 0)
            <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        @foreach ($profiles['headers'] as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profiles['rows'] as $row)
                        <tr>
                            @foreach ($profiles['headers'] as $h)
                                <td>
                                    @if ($h === 'segment')
                                        <span class="segment-pill">Segment {{ $row[$h] }}</span>
                                    @else
                                        {{ is_numeric($row[$h]) ? number_format((float) $row[$h], 2) : $row[$h] }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            <div class="alert alert-warning mb-0">No segment profile data found for this run.</div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">Visualizations</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                @if (file_exists($reader->elbowImagePath()))
                    <img src="{{ route('segmentation.image', [$run->run_uid, 'elbow_plot.png']) }}" class="img-fluid border rounded" alt="Elbow plot">
                @else
                    <div class="alert alert-warning mb-0">Elbow plot not available.</div>
                @endif
            </div>
            <div class="col-md-6">
                @if (file_exists($reader->pcaImagePath()))
                    <img src="{{ route('segmentation.image', [$run->run_uid, 'pca_clusters.png']) }}" class="img-fluid border rounded" alt="PCA cluster plot">
                @else
                    <div class="alert alert-warning mb-0">PCA cluster plot not available.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Segmented Data Preview
        @if ($totalRecords > 25)
            <span class="text-muted fw-normal">(first 25 of {{ $totalRecords }} rows)</span>
        @endif
    </div>
    <div class="card-body">
        @if ($dataPreview && count($dataPreview['rows']) > 0)
            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        @foreach ($dataPreview['headers'] as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataPreview['rows'] as $row)
                        <tr>
                            @foreach ($dataPreview['headers'] as $h)
                                <td>{{ $row[$h] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            <div class="alert alert-warning mb-0">No segmented data found for this run.</div>
        @endif
    </div>
</div>

@if (file_exists($reader->logPath()))
<div class="card">
    <div class="card-header">Run Log</div>
    <div class="card-body mb-0">
        <pre class="mb-0 small" style="max-height: 240px; overflow-y: auto;">{{ file_get_contents($reader->logPath()) }}</pre>
    </div>
</div>
@endif
@endsection
