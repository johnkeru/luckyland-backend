<?php

namespace App\Http\Controllers;

use App\Http\Responses\EmployeeLogsResponse;
use App\Models\EmployeeLogs;

class EmployeeLogsController extends Controller
{

    public function unReadLogs()
    {
        $unread = EmployeeLogs::where('visited', false)->count();
        return response()->json([
            'success' => true,
            'unread' => $unread
        ]);
    }

    public function employeeLogs($employeeId)
    {
        $empLogs = EmployeeLogs::where('user_id', $employeeId)->latest('created_at')->paginate(8);
        $unread = EmployeeLogs::where('visited', false)->where('user_id', $employeeId)->count();

        return new EmployeeLogsResponse($empLogs, $unread);
    }

    public function logsVisited($employeeId)
    {
        EmployeeLogs::where('user_id', $employeeId)->update(['visited' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.'
        ]);
    }
}
