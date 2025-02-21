<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
@extends('layouts.app')

@section('content')
<div class="container mx-auto my-8 px-4">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden flex flex-col md:flex-row">
        <div class="md:w-1/3">
            @if($room->room_pic)
                <img src="{{ asset('storage/'.$room->room_pic) }}" alt="Room Image" class="w-full h-full object-cover">
            @else
                <img src="{{ asset('images/default_room.jpg') }}" alt="Default Room Image" class="w-full h-full object-cover">
            @endif
        </div>
        <div class="md:w-2/3 p-6">
            <h2 class="text-3xl font-bold mb-4">{{ $room->room_name }}</h2>
            <p class="text-gray-700 mb-6">{{ $room->room_detail }}</p>
            <div class="mb-4">
                @if($room->room_status === 'available')
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">Available</span>
                @elseif($room->room_status === 'booked')
                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">Booked</span>
                @else
                    <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">Under Maintenance</span>
                @endif
            </div>
            <div class="mt-6">
                <a href="{{ route('rooms.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Back to Room List
                </a>
            </div>
            <div class="mt-6">
                <a href="#" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    จองห้องเลย
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

</body>
</html>