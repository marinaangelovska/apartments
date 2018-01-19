<?php

namespace App\Http\Controllers;


use App\Models\Apartment;
use App\Repository\Contracts\IApartmentsRepository;

use App\Repository\Contracts\IReservationsRepository;
use App\Repository\Contracts\IUsersRepository;
use Geocoder\Laravel\Facades\Geocoder;
use Illuminate\Http\Request;

class ApartmentsController extends Controller
{

    protected $repository;
    protected $reservationsRepository;
    protected $userRepository;

    /**
     * ApartmentsController constructor.
     */
    public function __construct(IApartmentsRepository $apartmentsRepository, IReservationsRepository $reservationsRepository, IUsersRepository $usersRepository)
    {
        $this->repository = $apartmentsRepository;
        $this->userRepository = $usersRepository;
        $this->reservationsRepository = $reservationsRepository;

    }

    public function getByLocation(Request $request){
        $allApartments = $this -> repository->getAll();
        $apartments = [];
        $availableApartmentsIds = [];
        $users = [];
        foreach($allApartments as $apartment){
            $reservations= $this -> reservationsRepository -> getAvailableApartmentsForPeriod($apartment->id, $request->from , $request->to);
            if(count($reservations) == 0){
                array_push($availableApartmentsIds, $apartment->id);
                array_push($users, $apartment->user_id);
            }
        }
        $apartmentObjects = $this->repository -> getAvailableApartmentsFromIdsArray($availableApartmentsIds);
        foreach($apartmentObjects as $apartment){
            $apartment["username"] = $this->userRepository->getById($apartment->user_id)->name;
        }

        return view('apartments.list')->with('apartments', $apartmentObjects);
    }

    public function show($id){
        return $this-> repository -> getById($id);
    }

    public function save(){
        return view('apartments.create');
    }

    public function create(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'address' => 'required'
        ]);
        $apartment = new Apartment();
        $apartment->name = $request->input('name');
        $apartment->description = $request->input('description');
        $location = Geocoder::geocode($request->input('address'))->get()->first()->getCoordinates();
        $apartment->longitude = $location->getLongitude();
        $apartment->latitude = $location->getLatitude();

        $result = $this->repository->create($apartment);
        if($result){
            return back()->with('success', 'Apartment has been added');
        }
        return $result;
    }

    public function edit(int $id){
        $apartment = $this->repository->getById($id);
        if($apartment == null){
            return response()->json(["error" => "404! Not Found"]);
        }
        $address = Geocoder::reverse($apartment->latitude, $apartment->longitude)->get()->first()->getFormattedAddress();
        return view('apartments.edit', compact('apartment', 'id', 'address'));
    }

    public function update(Request $request, $id){
        $validatedData = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'address' => 'required'
        ]);
        $apartment = new Apartment();
        $apartment->name = $request->get('name');
        $apartment->description = $request->get('description');
        $location = Geocoder::geocode($request->input('address'))->get()->first()->getCoordinates();
        $apartment->longitude = $location->getLongitude();
        $apartment->latitude = $location->getLatitude();
        $result = $this->repository->update($apartment, $id);
        if($result){
            return back()->with('success', 'Apartment has been updated');
        }
        return $result;
    }

}
