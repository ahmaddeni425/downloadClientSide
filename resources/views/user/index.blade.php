<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    
</head>
<body>
    <h1>User List</h1>

    <form action="/user" method="GET">
        <input type="text" name="email" placeholder="Search by email" value="{{ request('email') }}">
        <button type="submit">Search</button>
    </form>

    @include("user.export.index")

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    
</body>
</html>
