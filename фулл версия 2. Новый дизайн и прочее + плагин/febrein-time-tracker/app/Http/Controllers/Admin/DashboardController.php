<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserTimeLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');


        $query = UserTimeLog::orderBy('acted_at');


        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay()->subDay();
            $end   = Carbon::parse($endDate)->endOfDay()->addDay();
            $query->whereBetween('acted_at', [$start, $end]);
        }

        $logs = $query->get();


        $periodStart = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $periodEnd   = $endDate   ? Carbon::parse($endDate)->endOfDay()   : null;

        $userSessions = [];
        foreach ($logs as $log) {
            $uid = $log->user_id;
            if (!isset($userSessions[$uid])) {
                $userSessions[$uid] = [];
            }
            $userSessions[$uid][] = [
                'event_id' => $log->event_id,
                'acted_at' => Carbon::parse($log->acted_at),
            ];
        }

        $totalWork  = 0;
        $totalBreak = 0;
        $details    = [];


        $computedStart = $logs->count() ? $logs->first()->acted_at : null;
        $computedEnd   = $logs->count() ? $logs->last()->acted_at : null;

        foreach ($userSessions as $userId => $events) {
            $accWork   = 0;
            $accBreak  = 0;
            $startWork = null;
            $startBreak = null;

            foreach ($events as $ev) {
                $eid   = $ev['event_id'];
                $time  = $ev['acted_at'];

                if ($eid == 1) {
                    $startWork = $time;
                }
                elseif ($eid == 4) {
                    if ($startWork && $time->gte($startWork)) {
                        // clamp
                        $st = $this->clampTime($startWork, $periodStart, $periodEnd);
                        $sp = $this->clampTime($time, $periodStart, $periodEnd);
                        if ($sp->gt($st)) {
                            $accWork += $sp->diffInMinutes($st);
                        }
                    }
                    $startWork = null;
                }
                elseif ($eid == 2) {
                    $startBreak = $time;
                }
                elseif ($eid == 3) {
                    if ($startBreak && $time->gte($startBreak)) {
                        $sb = $this->clampTime($startBreak, $periodStart, $periodEnd);
                        $se = $this->clampTime($time, $periodStart, $periodEnd);
                        if ($se->gt($sb)) {
                            $accBreak += $se->diffInMinutes($sb);
                        }
                    }
                    $startBreak = null;
                }
            }

            // Незакрытый startWork
            if ($startWork && $computedEnd) {
                $st = $this->clampTime($startWork, $periodStart, $periodEnd);
                // Берём либо computedEnd, либо periodEnd
                $ce = Carbon::parse($computedEnd);
                $clampedEnd = $this->clampTime($ce, $periodStart, $periodEnd);
                if ($clampedEnd->gt($st)) {
                    $accWork += $clampedEnd->diffInMinutes($st);
                }
                $startWork = null;
            }
            // Незакрытый break
            if ($startBreak && $computedEnd) {
                $sb = $this->clampTime($startBreak, $periodStart, $periodEnd);
                $ce = Carbon::parse($computedEnd);
                $clampedEnd = $this->clampTime($ce, $periodStart, $periodEnd);
                if ($clampedEnd->gt($sb)) {
                    $accBreak += $clampedEnd->diffInMinutes($sb);
                }
                $startBreak = null;
            }

            $totalWork  += $accWork;
            $totalBreak += $accBreak;

            $details[] = [
                'user_id' => $userId,
                'work_minutes' => $accWork,
                'break_minutes' => $accBreak,
            ];
        }

        $finalStart = '';
        $finalEnd   = '';
        if ($startDate && $endDate) {
            $finalStart = Carbon::parse($startDate)->format('Y-m-d');
            $finalEnd   = Carbon::parse($endDate)->format('Y-m-d');
        } else {
            if ($computedStart) {
                $finalStart = Carbon::parse($computedStart)->format('Y-m-d');
            }
            if ($computedEnd) {
                $finalEnd = Carbon::parse($computedEnd)->format('Y-m-d');
            }
        }

        return response()->json([
            'total_work_minutes'  => $totalWork,
            'total_break_minutes' => $totalBreak,
            'start_date'          => $finalStart,
            'end_date'            => $finalEnd,
            'details'             => $details,
        ]);
    }

    private function clampTime(Carbon $time, ?Carbon $periodStart, ?Carbon $periodEnd): Carbon
    {
        $t = $time->copy();
        if ($periodStart && $t->lt($periodStart)) {
            $t = $periodStart->copy();
        }
        if ($periodEnd && $t->gt($periodEnd)) {
            $t = $periodEnd->copy();
        }
        return $t;
    }
}
