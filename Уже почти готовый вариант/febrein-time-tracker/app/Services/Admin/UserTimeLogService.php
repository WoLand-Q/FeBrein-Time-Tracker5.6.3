<?php

namespace App\Services\Admin;

use App\Models\UserTimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UserTimeLogService
{
    public function store(array $data):UserTimeLog
    {

        $userWorkDay = new UserWorkDayService();
        $userTimeLog = new UserTimeLog();
        if (!isset($data['acted_at']) || empty($data['acted_at'])) {
            $data['acted_at'] = (int)Carbon::now()->timestamp;
        }
        $userTimeLog->fill($data);
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            $userTimeLog->user_id = Auth::id();
            $data['user_id'] = Auth::id();
        }

        $dataWork = array();
        $dataWork['date'] = Carbon::createFromTimestamp($data['acted_at'])->toDateString();
        $dataWork['user_id']= $data['user_id'];

        $isUserTimeExist = UserTimeLog::whereDate('acted_at', $dataWork['date'])
            ->where('user_id', $dataWork['user_id'])
            ->where('event_id', $data['event_id'])
            ->exists();
        if($isUserTimeExist){
            throw new InvalidArgumentException("Already Exist");
        }

        try {
            $userTimeLog->save();
            $data['date'] = $dataWork['date'];
            if ((int)$data['event_id'] == 4) {
                $userWorkDay->storeAuto($data);
            }
            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollback();
            throw $exception;
        }

        return $userTimeLog;
    }

    public function update(UserTimeLog $userTimeLog, array $data): UserTimeLog
    {
        $userTimeLog->fill($data);
        $userTimeLog->save();

        return $userTimeLog;
    }

    public function destroy(UserTimeLog $userTimeLog): bool
    {
        return $userTimeLog->delete();
    }
}
