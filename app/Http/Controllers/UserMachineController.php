<?php

namespace App\Http\Controllers;

use App\UserMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserMachineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('is_admin')
            ->except('requestWorkMachine');
    }

    public function requestWorkMachine(Request $request)
    {
        if (!$request->machine_id) {
            return response()->json([
                'status' => 'required',
                'message' => 'Machine id is required'
            ], 422);
        }

        if ($um = UserMachine::find($request->machine_id)) {
            return response()->json([
                'status' => 'exists',
                'message' => $um->pending ?
                    'There\'s already a pending request sent from this machine' :
                    'This machine is already being used by someone'
            ], 422);
        }

        UserMachine::create([
            'machine_id' => $request->machine_id,
            'user_id' => auth()->id(),
        ]);
    }

    public function acceptWorkMachine(Request $request)
    {
        $userMachine = UserMachine::findOrFail($request->machine_id);
        if (!$userMachine->pending) {
            return response()->json([
                'status' => 'in_use',
                'message' => 'Machine exists and is already in use.'
            ]);
        }

        $userMachine->pending = false;
        $userMachine->save();

    }

    public function rejectWorkMachine(Request $request)
    {
        $userMachine = UserMachine::findOrFail($request->machine_id);
        if (!$userMachine->pending) {
            return response()->json([
                'status' => 'in_use',
                'message' => 'Machine exists and is already in use.'
            ]);
        }

        $userMachine->delete();
    }

    public function deleteWorkMachine(Request $request)
    {
        $userMachine = UserMachine::findOrFail($request->machine_id);
        if ($userMachine->pending) {
            abort(404);
        }

        $userMachine->delete();
    }
}
