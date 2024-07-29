<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
</head>
<body>
    <h1>User List</h1>

    <form action="/user" method="GET">
        <input type="text" name="email" placeholder="Search by email" value="{{ request('email') }}">
        <button type="submit">Search</button>
    </form>

    <button onclick="downloadCSV()">Download CSV</button>
    <button onclick="downloadExcel()">Download Excel</button>
    
    <p id="download-status"></p>

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

    <script>
        async function fetchAllData() {
            let params = new URLSearchParams(window.location.search);
            let apiUrl = '/api/user?' + params.toString();
            let allData = [];
            let page = 1;
            let total = 0;
            let currentPage = 1;

            while (apiUrl) {
                let response = await fetch(apiUrl);
                let json = await response.json();
                
                total = json.total;
                currentPage = json.current_page;

                allData = allData.concat(json.data);
                apiUrl = json.next_page_url;
                document.getElementById('download-status').innerText = `Download ${currentPage * 10} of ${total}`;
            }

            document.getElementById('download-status').innerText = `Download ${total} of ${total}`;
            return allData;
        }

        async function downloadCSV() {
            let data = await fetchAllData();
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Name,Email\n";
            data.forEach(user => {
                csvContent += `${user.name},${user.email}\n`;
            });

            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "users.csv");
            document.body.appendChild(link);
            link.click();

            document.getElementById('download-status').innerText = 'Download CSV';
        }

        async function downloadExcel() {
            let data = await fetchAllData();
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.json_to_sheet(data, { header: ["name", "email"] });
            XLSX.utils.book_append_sheet(wb, ws, "Users");

            XLSX.writeFile(wb, "users.xlsx");

            document.getElementById('download-status').innerText = 'Download Excel';
        }
    </script>
</body>
</html>
