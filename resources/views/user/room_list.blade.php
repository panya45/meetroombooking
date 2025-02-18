@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-5">
    <h2 class="text-2xl font-bold mb-4">Room List</h2>
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-4 py-2">#</th>
                <th class="border border-gray-300 px-4 py-2">Room Name</th>
                <th class="border border-gray-300 px-4 py-2">Detail</th>
                <th class="border border-gray-300 px-4 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($room_data as $room)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $room->id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $room->room_name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $room->room_detail }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        @if ($room->room_status === 'available')
                            <span class="text-green-500">Available</span>
                        @elseif ($room->room_status === 'booked')
                            <span class="text-red-500">Booked</span>
                        @else
                            <span class="text-yellow-500">Under Maintenance</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
