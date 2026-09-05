@extends('layouts.app')

@section('title', 'Run History')

@section('content')
@php $isAdmin = auth()->user()->isAdmin(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Run History</h1>
    <a href="{{ route('segmentation.create') }}" class="btn btn-crdb">New Segmentation</a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if ($runs->isEmpty())
            <div class="p-4 text-muted">No segmentation runs yet. Click "New Segmentation" to upload a dataset.</div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>File</th>
                        @if ($isAdmin) <th>User</th> @endif
                        <th>Created</th>
                        <th>k</th>
                        <th>Records</th>
                        <th>Segments</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($runs as $run)
                        <tr>
                            <td>{{ $run->original_filename }}</td>
                            @if ($isAdmin) <td>{{ $run->user->name ?? 'unknown' }}</td> @endif
                            <td>{{ $run->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $run->k ?? 'auto' }}</td>
                            <td>{{ $run->total_records ?? '—' }}</td>
                            <td>{{ $run->num_segments ?? '—' }}</td>
                            <td>
                                @php
                                    $badge = ['complete' => 'success', 'running' => 'info', 'pending' => 'secondary', 'failed' => 'danger'][$run->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($run->status) }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('segmentation.show', $run->run_uid) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <form action="{{ route('segmentation.destroy', $run->run_uid) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this run and all its files? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
