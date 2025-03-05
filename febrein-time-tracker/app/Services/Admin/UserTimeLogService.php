<?php

namespace App\Services\Admin;

use App\Models\User;
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
        if(isset(Auth::user()->role_id)|| !empty(Auth::user()->role_id)) {
            //dd(isset(Auth::user()->role_id),empty(Auth::user()->role_id));
            if (Auth::user()->role_id->value == 1) {
                if (!isset($data['acted_at']) || empty($data['acted_at'])) {
                    $data['acted_at'] = (int)Carbon::now()->timestamp;
                }
                $userTimeLog->fill($data);
                if (!isset($data['user_id']) || empty($data['user_id'])) {
                    $userTimeLog->user_id = Auth::id();
                    $data['user_id'] = Auth::id();
                }
            } else {
                $data['acted_at'] = (int)Carbon::now()->timestamp;
                $userTimeLog->fill($data);
                $userTimeLog->user_id = Auth::id();
                $data['user_id'] = Auth::id();
            }
        }else{
            $data['acted_at'] = (int)Carbon::now()->timestamp;
            $userTimeLog->fill($data);
            $userTimeLog->user_id = $data['user_id'];
        }

        $dataWork = array();
        $dataWork['date'] = Carbon::createFromTimestamp($data['acted_at'])->toDateString();
        //dd($dataWork['date']);
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

    public function autoSaveDay(): bool
    {
        $userIdsArray = User::pluck('id')->toArray();
        $date = Carbon::now()->toDateString();

        foreach ($userIdsArray as $userId) {
            $userTimeLog1 = UserTimeLog::whereDate('acted_at', $date)
                ->where('user_id', $userId)
                ->where('event_id', 1)
                ->exists();
            $userTimeLog2 = UserTimeLog::whereDate('acted_at', $date)
                ->where('user_id', $userId)
                ->where('event_id', 4)
                ->exists();

            //dd($userTimeLog1, $userTimeLog2);
            if($userTimeLog1 && !$userTimeLog2){
                $data=Array();
                $data['user_id'] = $userId;
                $data['acted_at'] = (int)Carbon::now()->timestamp;
                $data['event_id'] = 4;
                //dd($data);
                $this->store($data);
            }
        }
    return  true;
    }
}
