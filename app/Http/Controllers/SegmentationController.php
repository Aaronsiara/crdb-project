<?php

namespace App\Http\Controllers;

use App\Models\SegmentationRun;
use App\Services\SegmentationResultReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SegmentationController extends Controller
{
    /**
     * Lists past segmentation runs: the logged-in user's own runs, or
     * every run if the user is an admin.
     */
    public function index()
    {
        $query = SegmentationRun::query()->with('user')->orderByDesc('created_at');

        if (!Auth::user()->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        return view('segmentation.index', ['runs' => $query->get()]);
    }

    public function create()
    {
        return view('segmentation.upload');
    }

    /**
     * Handles the upload: creates a SegmentationRun DB row, saves the
     * file, runs the Python pipeline synchronously, then updates the row
     * with results (or the error message) before redirecting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:20480'], // 20MB
            'k' => ['nullable', 'integer', 'min:2', 'max:20'],
            'features' => ['nullable', 'regex:/^[a-zA-Z0-9_,\s]*$/', 'max:500'],
        ]);

        $runUid = now()->format('Ymd_His') . '_' . Str::random(6);
        $relativeDir = 'segmentation/' . $runUid;
        Storage::disk('local')->makeDirectory($relativeDir);
        $runDir = Storage::disk('local')->path($relativeDir);

        $run = SegmentationRun::create([
            'run_uid' => $runUid,
            'user_id' => Auth::id(),
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'k' => $validated['k'] ?? null,
            'features' => $validated['features'] ?? null,
            'status' => SegmentationRun::STATUS_RUNNING,
        ]);

        $ext = $request->file('file')->getClientOriginalExtension();
        $inputPath = $runDir . '/input.' . $ext;
        $request->file('file')->move($runDir, 'input.' . $ext);

        try {
            $this->runSegmentation($inputPath, $runDir, $validated['k'] ?? null, $validated['features'] ?? null);

            $reader = new SegmentationResultReader($runDir);
            $profiles = $reader->readCsv($reader->profilesPath());

            $run->update([
                'status' => SegmentationRun::STATUS_COMPLETE,
                'total_records' => $reader->countDataRows(),
                'num_segments' => $profiles ? count($profiles['rows']) : null,
                'completed_at' => now(),
            ]);

            session()->flash('success', 'Segmentation completed successfully.');
        } catch (\Throwable $e) {
            $run->update([
                'status' => SegmentationRun::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            session()->flash('error', 'Segmentation script failed: ' . $e->getMessage());
        }

        return redirect()->route('segmentation.show', $runUid);
    }

    /**
     * Displays the results dashboard for a specific run. Non-admins may
     * only view their own runs (enforced in checkOwnership() below).
     */
    public function show(string $run)
    {
        $runModel = $this->findRunOr404($run);
        $this->checkOwnership($runModel);

        $reader = new SegmentationResultReader($runModel->storagePath());
        $profiles = $reader->readCsv($reader->profilesPath());
        $dataPreview = $reader->readCsv($reader->dataPath(), 25);
        $totalRecords = $reader->countDataRows();

        return view('segmentation.show', [
            'run' => $runModel,
            'reader' => $reader,
            'profiles' => $profiles,
            'dataPreview' => $dataPreview,
            'totalRecords' => $totalRecords,
        ]);
    }

    /**
     * Serves generated plot images from storage/app (outside the public
     * webroot).
     */
    public function image(string $run, string $file)
    {
        $runModel = $this->findRunOr404($run);
        $this->checkOwnership($runModel);

        abort_unless(in_array($file, ['elbow_plot.png', 'pca_clusters.png'], true), 404);

        $path = $runModel->storagePath() . '/' . $file;
        abort_unless(file_exists($path), 404);

        return response()->file($path, ['Content-Type' => 'image/png']);
    }

    public function destroy(string $run)
    {
        $runModel = $this->findRunOr404($run);
        $this->checkOwnership($runModel);

        Storage::disk('local')->deleteDirectory('segmentation/' . $runModel->run_uid);
        $runModel->delete();

        session()->flash('success', 'Run deleted.');
        return redirect()->route('segmentation.index');
    }

    private function findRunOr404(string $runUid): SegmentationRun
    {
        abort_unless(preg_match('/^[a-zA-Z0-9_]+$/', $runUid), 404, 'Invalid run identifier.');
        return SegmentationRun::where('run_uid', $runUid)->firstOrFail();
    }

    private function checkOwnership(SegmentationRun $run): void
    {
        abort_unless(
            Auth::user()->isAdmin() || $run->user_id === Auth::id(),
            403,
            'You do not have access to this run.'
        );
    }

    /**
     * Runs the Python segmentation script using Laravel's Process facade
     * (wraps Symfony Process — arguments are escaped automatically, no
     * shell string concatenation).
     *
     * MPLCONFIGDIR is set explicitly because matplotlib tries to resolve
     * the current user's home directory (Path.home()) to find/create its
     * config cache. When Python is spawned by PHP/Apache on Windows, that
     * lookup can fail even though the same command works fine from a
     * normal terminal. Pointing MPLCONFIGDIR at the system temp folder
     * sidesteps the lookup entirely.
     */
    private function runSegmentation(string $inputPath, string $runDir, ?int $k, ?string $features): void
    {
        $pythonBin = config('segmentation.python_binary');
        $script = config('segmentation.script_path');

        $args = [$pythonBin, $script, '--input', $inputPath, '--output-dir', $runDir];

        if ($k) {
            $args[] = '--k';
            $args[] = (string) $k;
        } else {
            $args[] = '--auto-k';
        }

        if ($features) {
            $args[] = '--features';
            $args[] = $features;
        }

        $result = Process::timeout(300)
            ->env(['MPLCONFIGDIR' => sys_get_temp_dir()])
            ->run($args);

        file_put_contents(
            $runDir . '/run.log',
            "CMD: " . implode(' ', $args) . "\n\n" .
            "STDOUT:\n" . $result->output() . "\n\n" .
            "STDERR:\n" . $result->errorOutput()
        );

        if ($result->failed()) {
            throw new \RuntimeException('Python process exited with code ' . $result->exitCode() . '. See run.log for details.');
        }
    }
}
