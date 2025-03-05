<?php

namespace App\Services\Admin;

use App\Enums\RoleEnum;
use App\Models\UserTimeLog;
use App\Models\UserWorkDay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UserWorkDayService
{
    public function results(array $dataWork): array
    {
        $dataWork['work_minutes'] = ((int)$dataWork['work_ended_at'] - (int)$dataWork['work_started_at']) / 60;

        if(!isset($dataWork['break_started_at']) || empty($dataWork['break_started_at']))
        {
            $dataWork['break_minutes']=0;
        }
        else {
            if(!isset($dataWork['break_ended_at']) || empty($dataWork['break_ended_at']))
            {
                $dataWork['break_minutes'] = ((int)$dataWork['work_ended_at'] - (int)$dataWork['break_started_at']) / 60;
            }
            else
            $dataWork['break_minutes'] = ((int)$dataWork['break_ended_at'] - (int)$dataWork['break_started_at']) / 60;
        }

        return $dataWork;
    }

    public function storeAuto(array $dataWork): UserWorkDay
    {
        $userWorkDay = new UserWorkDay();
        $userTimeLogs = UserTimeLog::whereDate('acted_at', $dataWork['date'])
            ->where('user_id', $dataWork['user_id'])
            ->get();

        foreach ($userTimeLogs as $userTimeLog) {
            switch ($userTimeLog->event_id->value) {
                case 1:
                    $dataWork['work_started_at'] = $userTimeLog->acted_at->timestamp;
                    break;
                case 2:
                    $dataWork['break_started_at'] = $userTimeLog->acted_at->timestamp;
                    break;
                case 3:
                    $dataWork['break_ended_at'] = $userTimeLog->acted_at->timestamp;
                    break;
                case 4:
                    $dataWork['work_ended_at'] = $userTimeLog->acted_at->timestamp;
                    break;
                default:
                    break;
            }
        }

        $dataWork = $this->results($dataWork);
        $userWorkDay->fill($dataWork);
        $userWorkDay->save();
        return $userWorkDay;
    }

    public function store(array $data): UserWorkDay
    {
        $userWorkDay = new UserWorkDay();

        $data['date'] = Carbon::createFromTimestamp($data['work_started_at'])->toDateString();

        $userTimeLogExists = UserWorkDay::whereDate('date', $data['date'])
            ->where('user_id', $data['user_id'])
            ->exists();
        if($userTimeLogExists){
            throw new InvalidArgumentException("Already Exist");
        }

        $data = $this->results($data);
        $userWorkDay->fill($data);
        $userWorkDay->save();

        return $userWorkDay;
    }

    public function update(UserWorkDay $userWorkDay, array $data): UserWorkDay
    {
        $data = $this->results($data);
        $userWorkDay->fill($data);
        $userWorkDay->save();

        return $userWorkDay;
    }

    public function destroy(UserWorkDay $userWorkDay): bool
    {
        return $userWorkDay->delete();
    }


}
