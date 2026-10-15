<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Requests\PackageRequest;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
class RoomController extends Controller
{
    public function __construct(private RoomService $roomService){}
    public function index():JsonResponse
    {
        $rooms=$this->roomService->getAllRooms();
        return response()->json([
            'success'=>true,
            'data'=>$rooms->items(),
            'pagination'=>[
                'total'=>$rooms->total(),
                'per_page'=>$rooms->perPage(),
                'current_page'=>$rooms->currentPage(),
                'last_page'=>$rooms->lastPage(),
            ]
        ]);
    }
    public function show(int $id):JsonResponse
    {
        $room=$this->roomService->getRoomById($id);
        if(!$room){
            return response()->json([
                'success'=>false,
                'message'=>'Ruangan tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'data'=>$room
        ]);
    }
    public function store(RoomRequest $request):JsonResponse
    {
        $room=$this->roomService->createRoom($request->validated());
        return response()->json([
            'success'=>true,
            'message'=>'Ruangan berhasil dibuat',
            'data'=>$room
        ],201);
    }
    public function update(int $id,RoomRequest $request):JsonResponse
    {
        $room=$this->roomService->updateRoom($id,$request->validated());
        if(!$room){
            return response()->json([
                'success'=>false,
                'message'=>'Ruangan tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Ruangan berhasil diperbarui',
            'data'=>$room
        ]);
    }
    public function destroy(int $id):JsonResponse
    {
        $deleted=$this->roomService->deleteRoom($id);
        if(!$deleted){
            return response()->json([
                'success'=>false,
                'message'=>'Ruangan tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Ruangan berhasil dihapus'
        ]);
    }
    public function getPackages(int $roomId):JsonResponse
    {
        $packages=$this->roomService->getRoomPackages($roomId);
        return response()->json([
            'success'=>true,
            'data'=>$packages
        ]);
    }
    public function storePackage(int $roomId,PackageRequest $request):JsonResponse
    {
        $package=$this->roomService->createPackage($roomId,$request->validated());
        return response()->json([
            'success'=>true,
            'message'=>'Paket berhasil dibuat',
            'data'=>$package
        ],201);
    }
    public function updatePackage(int $packageId,PackageRequest $request):JsonResponse
    {
        $package=$this->roomService->updatePackage($packageId,$request->validated());
        if(!$package){
            return response()->json([
                'success'=>false,
                'message'=>'Paket tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Paket berhasil diperbarui',
            'data'=>$package
        ]);
    }
    public function destroyPackage(int $packageId):JsonResponse
    {
        $deleted=$this->roomService->deletePackage($packageId);
        if(!$deleted){
            return response()->json([
                'success'=>false,
                'message'=>'Paket tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Paket berhasil dihapus'
        ]);
    }
}