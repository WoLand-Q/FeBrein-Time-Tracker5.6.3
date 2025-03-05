<?php

namespace app\Services\Admin;

use App\Models\Group;

class GroupService
{
    public function store(array $data): Group
    {
        $group = new Group();
        $group->fill($data);
        // Если нужно установить creator_id или другие поля
        // $group->creator_id = Auth::id();
        $group->save();

        return $group;
    }

    public function update(Group $group, array $data): Group
    {
        $group->fill($data);
        $group->save();

        return $group;
    }

    public function destroy(Group $group): bool
    {
        return $group->delete();
    }
}
