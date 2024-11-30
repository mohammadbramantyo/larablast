@extends('layouts.app')

@section('title', 'History')


@section('content')
<h1 class="text-2xl font-bold text-center mb-6">History Records</h1>
<div class="overflow-x-auto shadow-md sm:rounded-lg">
    <table class="min-w-full border-collapse bg-white text-left text-sm text-gray-700">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-4 font-medium text-gray-900">#</th>
                <th class="px-6 py-4 font-medium text-gray-900">Date</th>
                <th class="px-6 py-4 font-medium text-gray-900">Saved Rows</th>
                <th class="px-6 py-4 font-medium text-gray-900">Processed Rows</th>
                <th class="px-6 py-4 font-medium text-gray-900">Duplicate Rows</th>
                <th class="px-6 py-4 font-medium text-gray-900">Valid Rows</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach ($histories as $index => $history)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">{{ $index + 1 }}</td>
                <td class="px-6 py-4">{{ $history->created_at->format('M j, Y g:i A') }}</td>
                <td class="px-6 py-4">{{ $history->saved_rows }}</td>
                <td class="px-6 py-4">{{ $history->processed_rows }}</td>
                <td class="px-6 py-4">{{ $history->duplicate_rows }}</td>
                <td class="px-6 py-4">{{ $history->valid_rows }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection