@extends('layouts.app')
@section('title','User Management')
@section('content')
<h1 class="text-2xl font-semibold mb-4">All Users</h1>
<table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Name</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Email</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Tenant Status</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 text-sm text-gray-800 border-b">{{ $user->username }}</td>
            <td class="px-4 py-2 text-sm text-gray-800 border-b">{{ $user->email }}</td>
            <td class="px-4 py-2 text-sm text-gray-800 border-b">{{ $user->is_subscribed ? 'subscribed' : 'not-subscribed' }}</td>
            <td class="px-4 py-2 text-sm text-blue-600 hover:underline border-b">
                <form action="/subscribe/user/{{ $user->id }}" method="POST">
                    @csrf <!-- Add CSRF token for Laravel security -->
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Subscribe
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection