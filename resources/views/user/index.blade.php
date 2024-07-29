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
        // ini nanti kamu bisa edit untuk tambahkan jeda
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // ini untuk ambil datanya 
        async function fetchAllData() {
            let params = new URLSearchParams(window.location.search);
            let apiUrl = '/api/user?' + params.toString();
            let allData = [];
            let total = 0;
            let perPage = 0;
            let currentPage = 1;

            while (apiUrl) {
                let response = await fetch(apiUrl);
                let json = await response.json();
                
                total = json.total;
                perPage = json.per_page;
                currentPage = json.current_page;

                allData = allData.concat(json.data);
                apiUrl = json.next_page_url;

                document.getElementById('download-status').innerText = `Download ${Math.min(currentPage * perPage, total)} of ${total}`;

                await sleep(1000);
            }

            document.getElementById('download-status').innerText = `Download ${total} of ${total}`;
            return allData;
        }

        //lalu ini buat setup headernya
        function defineHeader() {
            return ['Name', 'Email', 'Email Verified At', 'Created At', 'Old Member'];
        }

        //ini untuk format datanya jika kamu perlu
        function formatData(row) {
            const cutoffDate = new Date("2024-07-29T03:00:00Z");
            const createdDate = new Date(row.created_at);
            return [
                row.name || "",
                row.email || "",
                row.email_verified_at || "",
                row.created_at || "",
                createdDate < cutoffDate ? "Yes" : "No"
            ];
        }

        // ini buat generate csv
        function generateCSV(data) {
            let csvContent = "data:text/csv;charset=utf-8,";
            const header = defineHeader();
            csvContent += header.join(",") + "\n";
            data.forEach(row => {
                let rowData = formatData(row);
                csvContent += rowData.join(",") + "\n";
            });
            return csvContent;
        }

        // ini buat generate excel
        function generateExcel(data) {
            let wb = XLSX.utils.book_new();
            let header = defineHeader();
            let wsData = data.map(row => formatData(row));
            wsData.unshift(header); // Add header row
            let ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Users");
            return wb;
        }

        // ini buat download csv
        async function downloadCSV() {
            let data = await fetchAllData();
            let csvContent = generateCSV(data);
            
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "users.csv");
            document.body.appendChild(link);
            link.click();

            document.getElementById('download-status').innerText = 'Download CSV';
        }

        // ini buat download excel
        async function downloadExcel() {
            let data = await fetchAllData();
            let wb = generateExcel(data);
            
            XLSX.writeFile(wb, "users.xlsx");

            document.getElementById('download-status').innerText = 'Download Excel';
        }
    </script>
</body>
</html>
