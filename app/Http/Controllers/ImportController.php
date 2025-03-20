<?php

namespace App\Http\Controllers;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ImportController extends Controller
{
    public function getTableWiseFields($table)
    {
        $error = '';
        $route = ''; // Initialize route to avoid undefined variable warnings
        $fields = []; // Initialize fields
    
        if ($table === 'customers') {
            $extraFields = ['id', 'customer_id', 'avatar', 'is_active', 'email_verified_at', 'lang', 'remember_token', 'created_by', 'created_at', 'updated_at'];
            $tableFields = Utility::getTableFields($table, $extraFields);
            
            if ($tableFields['status']) {
                $route = "customer.import.data";
                $fields = $tableFields['data'];
            } else {
                $error = $tableFields['message'] ?? 'Failed to retrieve table fields.';
            }
        } else {
            $error = 'Invalid table name. Only customer import is supported.';
        }
    
        Log::info('getTableWiseFields Response:', [
            'table' => $table,
            'route' => $route,
            'fields' => $fields,
            'error' => $error
        ]);
    
        return [
            'route' => $route,
            'fields' => $fields,
            'error' => $error,
        ];
    }
    

    // public function fileImport(Request $request)
    // {
    //     session_start();

    //     $error = '';

    //     $html = '';

    //     $fields = [];
    //     $route = '';

    //     if ($request->hasFile('file') && $request->file->getClientOriginalName() != '') {
    //         $file_array = explode(".", $request->file->getClientOriginalName());

    //         $extension = end($file_array);
    //         if ($extension == 'csv') {
    //             $file_data = fopen($request->file->getRealPath(), 'r');
    //             $file_header = fgetcsv($file_data);

    //             $tableFields = $this->getTableWiseFields($request->table);
    //             if ($tableFields['error'] != '') {
    //                 $error = $tableFields['error'];
    //             } else {
    //                 $fields = $tableFields['fields'];
    //             }

    //             $limit = 0;
    //             $temp_data = [];
    //             while (($row = fgetcsv($file_data)) !== false) {
    //                 $limit++;
    //                 $html .= '<tr>';
    //                 for ($count = 0; $count < count($row); $count++) {
    //                     $html .= '<td>' . $row[$count] . '</td>';
    //                 }
    //                 $html .= '</tr>';
    //                 $temp_data[] = $row;
    //             }
    //             $_SESSION['file_data'] = $temp_data;
    //         } else {
    //             $error = 'Only <b>.csv</b> file allowed';
    //         }
    //     } else {

    //         $error = 'Please Select CSV File';
    //     }
    //     $output = array(
    //         'error' => $error,
    //         'output' => $html,
    //         'fields' => $fields,
    //     );

    //     return json_encode($output);
    // }
    public function fileImport(Request $request)
    {
        session_start();
        Log::info('fileImport function started.');
    
        $error = '';
        $html = '';
        $fields = [];
        $route = '';
    
        try {
            if ($request->hasFile('file') && $request->file->getClientOriginalName() != '') {
                Log::info('File detected: ' . $request->file->getClientOriginalName());
    
                $file_array = explode(".", $request->file->getClientOriginalName());
                $extension = end($file_array);
                Log::info('File extension: ' . $extension);
    
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');
                    Log::info('CSV file opened successfully.');
    
                    $file_header = fgetcsv($file_data);
                    Log::info('CSV Header:', $file_header);
    
                    // $tableFields = $this->getTableWiseFields($request->table);
                    // if ($tableFields['error'] != '') {
                    //     $error = $tableFields['error'];
                    //     Log::error('Error in getTableWiseFields: ' . $error);
                    // } else {
                    //     $fields = $tableFields['fields'];
                    //     Log::info('Table fields retrieved successfully.');
                    // }
    
                    $limit = 0;
                    $temp_data = [];
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;
                        Log::info('Processing row ' . $limit, $row);
    
                        $html .= '<tr>';
                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }
                        $html .= '</tr>';
                        $temp_data[] = $row;
                    }
    
                    // $_SESSION['file_data'] = $temp_data;
                    session()->put('file_data', $temp_data);
                    // Log::info('File data stored in session.', ['rows' => count($temp_data)]);
                    // // ✅ Call customerImportdata directly after storing session
                    Log::info('Calling customer.import.data route...');
    
                    $response = app()->call('App\Http\Controllers\CustomerController@customerImportdata');
                    
                    return response()->json($response);
                    // return $this->customerImportdata($request);
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                    Log::error($error);
                }
            } else {
                $error = 'Please Select CSV File';
                Log::error($error);
            }
        } catch (\Exception $e) {
            Log::error('Exception in fileImport: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $error = 'An error occurred while processing the file. Check logs for details.';
        }
    
        $output = [
            'error' => $error,
            'output' => $html,
            'fields' => $fields,
            'route' => $route,
        ];
    
        Log::info('fileImport function completed.', $output);
    
        return json_encode($output);
    }
    public function fileImportModal(Request $request)
    {
        $fields = [];
        $route  = '';
        $tableFields = $this->getTableWiseFields($request->table);
        if ($tableFields['error'] != '') {
            $error = $tableFields['error'];
        } else {
            $fields = json_encode($tableFields['fields']);
            $route = $tableFields['route'];
        }
        return view('import.import_modal', compact('fields', 'route'));
    }
}
