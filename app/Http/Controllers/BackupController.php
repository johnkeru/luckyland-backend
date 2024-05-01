<?php

namespace App\Http\Controllers;

use App\Http\Responses\BackupIndexResponse;
use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupController extends Controller
{

    public function backupv2()
    {
        // Call the Artisan command
        Artisan::call('backup:run');

        // Get the output of the command
        $output = Artisan::output();

        // You can do something with the output if needed
        // For example, you can return it to the user
        return response()->json(['message' => 'Backup completed.', 'output' => $output]);
    }

    public function index()
    {
        try {
            $backups = Backup::latest()->paginate(9);
            return new BackupIndexResponse($backups);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving backups.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function backup()
    {
        try {
            // Generate the fixed date format
            $dateFormat = date('Y-m-d-H-i-s');
            $appName = env('APP_NAME');

            // Generate the filename
            $filename = $dateFormat . '.zip';
            $fullPath = 'app/backups/' . $appName . '/' . $filename;

            // Create a Zip archive
            $zip = new ZipArchive();
            $zip->open(storage_path($fullPath), ZipArchive::CREATE);

            // Add the database dump to the archive
            $zip->addFromString('db-dumps/mysql-luckyland.sql', $this->getDatabaseDump());

            // Close the archive
            $zip->close();

            // Save backup details to the database
            $backup = new Backup();
            $backup->filename = $filename;
            $backup->size = filesize(storage_path($fullPath));
            $backup->status = 'created'; // or any other status you want to set
            $backup->checksum = md5_file(storage_path($fullPath));
            $backup->storage_location = $fullPath;
            $backup->save();

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred while creating the backup.'
            ], 500);
        }
    }

    private function getDatabaseDump()
    {
        // Execute the database dump command and capture the output
        $output = shell_exec('mysqldump -u ' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' ' . env('DB_DATABASE'));
        return $output;
    }


    public function download(Backup $backup)
    {
        try {
            $filePath = storage_path($backup->storage_location);

            return response()->download($filePath, $backup->filename);
        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred while downloading the backup'
            ], 500);
        }
    }
}






















    // public function backup(Request $request)
    // {
    //     try {
    //         // Generate the fixed date format
    //         $dateFormat = date('Y-m-d-H-i-s');
    //         $appName = env('APP_NAME');

    //         // Generate the filename
    //         $filename = $dateFormat . '.zip';
    //         $fullPath = 'app/backups/' . $appName . '/' . $filename;

    //         // Create a Zip archive
    //         $zip = new ZipArchive();
    //         $zip->open(storage_path($fullPath), ZipArchive::CREATE);

    //         // Add the database dump to the archive
    //         $zip->addFromString('db-dumps/mysql-luckyland.sql', $this->getDatabaseDump());

    //         // Add any other files or directories i want to backup
    //         // $zip->addFile('path/to/file.txt', 'file.txt');
    //         // $zip->addEmptyDir('path/to/directory');

    //         // Close the archive
    //         $zip->close();

    //         // if download instead of saving in the storage.
    //         // return response()->download(storage_path($fullPath))->deleteFileAfterSend();

    //         // Return a success response
    //         return response()->json([
    //             'message' => 'Backup created successfully',
    //             'filename' => $filename,
    //         ]);
    //     } catch (\Exception $e) {
    //         // Log the error
    //         Log::error('Error creating backup: ' . $e->getMessage());

    //         // Return an error response
    //         return response()->json([
    //             'message' => 'An error occurred while creating the backup.' . $e->getMessage(),
    //         ]);
    //     }
    // }