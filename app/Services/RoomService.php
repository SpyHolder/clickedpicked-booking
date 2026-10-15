<?php
namespace App\Services;
use App\Models\Room;
use App\Models\RoomPackage;
use App\Enums\RoomStatus;
use Illuminate\Pagination\Paginator;
class RoomService
{
    public function getAllRooms(int $perPage=15):Paginator
    {
        return Room::paginate($perPage);
    }
    public function getRoomById(int $id):?Room
    {
        return Room::with(['images','packages'])->find($id);
    }
    public function createRoom(array $data):Room
    {
        return Room::create([
            'kode_ruangan'=>$this->generateRoomCode(),
            'nama_ruangan'=>$data['nama_ruangan'],
            'deskripsi'=>$data['deskripsi']??null,
            'kapasitas'=>$data['kapasitas']??1,
            'lokasi'=>$data['lokasi']??null,
            'status_ruangan'=>RoomStatus::Active,
        ]);
    }
    public function updateRoom(int $id,array $data):?Room
    {
        $room=Room::find($id);
        if(!$room)return null;
        $room->update([
            'nama_ruangan'=>$data['nama_ruangan']??$room->nama_ruangan,
            'deskripsi'=>$data['deskripsi']??$room->deskripsi,
            'kapasitas'=>$data['kapasitas']??$room->kapasitas,
            'lokasi'=>$data['lokasi']??$room->lokasi,
            'status_ruangan'=>$data['status_ruangan']??$room->status_ruangan,
        ]);
        return $room;
    }
    public function deleteRoom(int $id):bool
    {
        $room=Room::find($id);
        if(!$room)return false;
        return $room->delete();
    }
    public function getRoomPackages(int $roomId):mixed
    {
        return RoomPackage::where('id_room',$roomId)->get();
    }
    public function createPackage(int $roomId,array $data):RoomPackage
    {
        return RoomPackage::create([
            'id_room'=>$roomId,
            'nama_paket'=>$data['nama_paket'],
            'durasi_jam'=>$data['durasi_jam'],
            'harga'=>$data['harga'],
            'inklusi'=>$data['inklusi']??null,
        ]);
    }
    public function updatePackage(int $packageId,array $data):?RoomPackage
    {
        $package=RoomPackage::find($packageId);
        if(!$package)return null;
        $package->update([
            'nama_paket'=>$data['nama_paket']??$package->nama_paket,
            'durasi_jam'=>$data['durasi_jam']??$package->durasi_jam,
            'harga'=>$data['harga']??$package->harga,
            'inklusi'=>$data['inklusi']??$package->inklusi,
        ]);
        return $package;
    }
    public function deletePackage(int $packageId):bool
    {
        $package=RoomPackage::find($packageId);
        if(!$package)return false;
        return $package->delete();
    }
    private function generateRoomCode():string
    {
        do{
            $code='ROOM-'.strtoupper(bin2hex(random_bytes(3)));
        }while(Room::where('kode_ruangan',$code)->exists());
        return $code;
    }
}