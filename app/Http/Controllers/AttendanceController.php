<?php

namespace App\Http\Controllers;

use App\Models\User;

class AttendanceController extends Controller
{
    public function userShow(User $user)
    {
        $projects = $user->projects()->get();
        $individual_bonus_percent = $user->individual_bonus_percent;
        $totalContractAmount = $projects->sum('contract_amount');
        $projectsCount = $user->projects()->count();

        return view('admin.attendance.userShow', compact('user', 'totalContractAmount', 'projectsCount'));
    }
}
