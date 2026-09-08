<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// 1. Make sure this import is at the top of your controller file
use Illuminate\Support\Facades\Process; 

class SegmentationController extends Controller
{
    public function runSegmentation()
    {
        // 2. Put Option 1 right here inside your method
        $result = Process::withEnvironment([
            'USERPROFILE' => 'C:\xampp\tmp',
            'MPLCONFIGDIR' => 'C:\xampp\tmp',
        ])->run([
            'E:/anaconda/python.exe',
            'C:\xampp\htdocs\crdb-segmentation\scripts\segment_any_data.py',
            '--input', 'C:\xampp\htdocs\crdb-segmentation\storage\app/private/segmentation/20260908_064754_Bciu1a/input.csv',
            '--output-dir', 'C:\xampp\htdocs\crdb-segmentation\storage\app/private/segmentation/20260908_064754_Bciu1a',
            '--k', '10'
        ]);

        if ($result->successful()) {
            return response()->json(['message' => 'Success!', 'output' => $result->output()]);
        }

        return response()->json(['error' => $result->errorOutput()], 500);
    }
}
