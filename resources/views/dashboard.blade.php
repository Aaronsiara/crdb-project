@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="h4 mb-1">Welcome, {{ auth()->user()->name }}</h1>
<p class="text-muted mb-4">CRDB Bank Tanzania — Customer Segmentation Dashboard</p>

<div class="row mb-1">
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Total Runs</div>
            <div class="fs-3 fw-bold text-primary">{{ $totalRuns }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Completed</div>
            <div class="fs-3 fw-bold text-primary">{{ $completeRuns }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small text-uppercase">Records Segmented</div>
            <div class="fs-3 fw-bold text-primary">{{ number_format((int) $totalRecordsSegmented) }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        Recent Segmentation Runs
        <a href="{{ route('segmentation.create') }}" class="btn btn-sm btn-crdb">New Segmentation</a>
    </div>
    <div class="card-body p-0">
        @if ($recentRuns->isEmpty())
            <div class="p-4 text-muted">No runs yet. Click "New Segmentation" to upload your first dataset.</div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Created</th>
                        <th>Records</th>
                        <th>Segments</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentRuns as $run)
                        <tr>
                            <td>{{ $run->original_filename }}</td>
                            <td>{{ $run->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $run->total_records ?? '—' }}</td>
                            <td>{{ $run->num_segments ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = ['complete' => 'success', 'running' => 'info', 'pending' => 'secondary', 'failed' => 'danger'][$run->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($run->status) }}</span>
                            </td>
                            <td><a href="{{ route('segmentation.show', $run->run_uid) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
