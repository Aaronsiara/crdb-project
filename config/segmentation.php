<?php

return [
    // Path to your Python 3 interpreter. On Windows with Anaconda this is
    // often something like:
    //   C:\Users\<you>\anaconda3\python.exe
    // Set this via the PYTHON_BINARY key in your .env file.
    'python_binary' => env('PYTHON_BINARY', 'python3'),

    // Path to the segmentation script. Defaults to base_path('scripts/segment_any_data.py'),
    // i.e. the scripts/ folder in your project root.
    'script_path' => env('SEGMENTATION_SCRIPT', base_path('scripts/segment_any_data.py')),
];
