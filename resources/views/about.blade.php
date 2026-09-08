@extends('layouts.app')

@section('title', 'About / Project History')

@section('content')
<h1 class="h4 mb-3">About This Project</h1>

<div class="card">
    <div class="card-header">Purpose</div>
    <div class="card-body">
        <p>
            This application was built during field work with CRDB Bank Tanzania's
            Data Department. It applies unsupervised machine learning — Principal
            Component Analysis (PCA) and K-Means clustering — to group retail and
            SimBanking customers into behavioral segments based on transaction
            frequency, average transaction value, mobile banking activity, savings
            balance, loan activity, and recency of use.
        </p>
        <p class="mb-0">
            The goal: give the Data Department a reusable, self-service tool for
            turning raw customer activity data into segments that can inform product
            design, targeted financial-inclusion campaigns, and dormancy
            interventions — without needing a data scientist to run a notebook by
            hand each time.
        </p>
    </div>

</div>

<div class="card">
    <div class="card-header">How It Works</div>
    <div class="card-body">
        <ol class="mb-0">
            <li><strong>Upload</strong> — a CSV or Excel export of customer records is uploaded through this dashboard.</li>
            <li><strong>Feature scaling</strong> — numeric columns are standardized so no single feature (e.g. balance in TZS) dominates the clustering distance metric.</li>
            <li><strong>Dimensionality reduction</strong> — PCA reduces the feature space to two components for visualization and to check for multicollinearity.</li>
            <li><strong>Clustering</strong> — K-Means groups customers into segments; the number of segments (k) can be set manually or auto-selected via an elbow heuristic.</li>
            <li><strong>Profiling</strong> — each segment's average behavior is summarized into a business-readable table, alongside the labeled dataset and diagnostic plots.</li>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">Architecture</div>
    <div class="card-body">
        <p>The system is split into two layers:</p>
        <ul>
            <li>
                <strong>Laravel (PHP)</strong> — handles authentication, the upload
                interface, run history (stored in a MySQL database), and the
                results dashboard. This is the layer users interact with.
            </li>
            <li>
                <strong>Python pipeline</strong> — a standalone script
                (<code>segment_any_data.py</code>) using pandas, scikit-learn,
                and matplotlib, invoked by the Laravel backend for each upload
                via the Process facade. It does the actual scaling, PCA, and
                K-Means work, and writes its results back out as CSVs and PNG
                plots.
            </li>
        </ul>
        <p class="mb-0">
            Every upload is recorded as a row in the <code>segmentation_runs</code>
            database table (who uploaded it, when, with what parameters, and its
            status), so run history persists independently of the filesystem and
            supports multiple users with separate histories.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">Project Timeline</div>
    <div class="card-body">
        <ul class="mb-0">
            <li>Prototyped the segmentation approach (PCA + K-Means) on synthetic customer data to validate the pipeline end-to-end.</li>
            <li>Generalized the pipeline into a standalone script that accepts any CSV/Excel input with configurable feature columns and cluster count.</li>
            <li>Built a PHP dashboard to present results without requiring stakeholders to open a notebook.</li>
            <li>Rebuilt the interface as a full Laravel application, adding authentication, a database-backed run history, and a proper sidebar dashboard.</li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header">Author</div>
    <div class="card-body mb-0">
        <p class="mb-0">
            Built by Aaron, a Data Science student at the Eastern Africa
            Statistical Training Centre (EASTC), Dar es Salaam, during field
            work placement with CRDB Bank Tanzania's Data Department.
        </p>
    </div>
</div>
@endsection
