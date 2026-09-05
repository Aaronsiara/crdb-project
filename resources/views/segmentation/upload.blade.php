@extends('layouts.app')

@section('title', 'New Segmentation Run')

@section('content')
<h1 class="h4 mb-3">New Segmentation Run</h1>

<div class="card">
    <div class="card-header">Upload Customer Data</div>
    <div class="card-body">
        <form method="POST" action="{{ route('segmentation.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="file" class="form-label">Customer Data File (CSV or Excel)</label>
                <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="k" class="form-label">Number of Segments (k)</label>
                <input type="number" name="k" id="k" min="2" max="20" placeholder="e.g. 5"
                       class="form-control @error('k') is-invalid @enderror" value="{{ old('k') }}">
                <div class="form-text">Leave blank to let the pipeline auto-select k using an elbow heuristic.</div>
                @error('k')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="features" class="form-label">Feature Columns (comma-separated)</label>
                <input type="text" name="features" id="features" placeholder="e.g. txn_freq_month,avg_txn_value,savings_balance"
                       class="form-control @error('features') is-invalid @enderror" value="{{ old('features') }}">
                <div class="form-text">Leave blank to automatically use every numeric column in the file.</div>
                @error('features')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-crdb">Run Segmentation</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">How this works</div>
    <div class="card-body mb-0">
        <ol class="mb-0">
            <li>Your file is saved and passed to a Python pipeline (<code>segment_any_data.py</code>).</li>
            <li>The pipeline scales your numeric features, reduces them with PCA, and clusters
                customers using K-Means.</li>
            <li>A record of this run is saved to the database, and you're redirected to a results
                dashboard: segment profiles, an elbow plot, a PCA scatter plot, and a preview of
                the labeled data.</li>
        </ol>
    </div>
</div>
@endsection
