<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Department;

class RoomController extends Controller
{
    public function addRoom(Request $request)
    {
        Room::create([
            'room_name' => $request->room_name
        ]);

        $rooms = Room::select("rooms.id", "rooms.room_name", "department.department_name_abbreviation")
            ->leftJoin('department', 'rooms.department_id', '=', 'department.id')
            ->get();

        return response(['message' => 'success', 'rooms' => $rooms]);
    }

    public function getRooms()
    {
        $rooms = Room::select("rooms.id", "rooms.room_name", "department.department_name_abbreviation")
            ->leftJoin('department', 'rooms.department_id', '=', 'department.id')
            ->get();

        $depatments = Department::select("id", "department_name_abbreviation")
            ->with(['Room' => function ($query) {
                $query->select('id', 'department_id', 'room_name');
            }])->get();

        return response(['rooms' => $rooms, 'department' => $depatments]);
    }

    public function assignRoom(Request $request)
    {
        Room::where('id', $request->roomId)
            ->update([
                'department_id' => $request->departmentId
            ]);

        $rooms = Room::select("rooms.id", "rooms.room_name", "department.department_name_abbreviation")
            ->leftJoin('department', 'rooms.department_id', '=', 'department.id')
            ->get();

        $depatments = Department::select("id", "department_name_abbreviation")
            ->with(['Room' => function ($query) {
                $query->select('id', 'department_id', 'room_name');
            }])->get();
        return response(['message' => 'success', 'rooms' => $rooms, 'department' => $depatments]);
    }

    public function unassignRoom($id) 
    {
        Room::where('id', $id)
            ->update([
                'department_id' => null
            ]);

        $rooms = Room::select("rooms.id", "rooms.room_name", "department.department_name_abbreviation")
            ->leftJoin('department', 'rooms.department_id', '=', 'department.id')
            ->get();

        $depatments = Department::select("id", "department_name_abbreviation")
            ->with(['Room' => function ($query) {
                $query->select('id', 'department_id', 'room_name');
            }])->get();

        return response(['message' => 'success', 'rooms' => $rooms, 'department' => $depatments]);
    }
}
