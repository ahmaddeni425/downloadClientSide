<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
<button onclick="downloadCSV()">Download CSV</button>
<button onclick="downloadExcel()">Download Excel</button>

<p id="download-status"></p>

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
            try {
                let json = await fetchWithRetry(apiUrl, 10, 5000); // retry 10 kali tiap 5 detik
                total = json.total;
                perPage = json.per_page;
                currentPage = json.current_page;

                allData = allData.concat(json.data);
                apiUrl = json.next_page_url;

                document.getElementById('download-status').innerText = `Download ${Math.min(currentPage * perPage, total)} of ${total}`;
                await sleep(500);
            } catch (err) {
                document.getElementById('download-status').innerText = `Gagal download data: ${err.message}`;
                break;
            }
        }

        if (allData.length === total) {
            document.getElementById('download-status').innerText = `Download selesai (${total} data)`;
        }

        return allData;
    }

    async function fetchWithRetry(url, retries = 5, delay = 3000) {
        for (let attempt = 0; attempt < retries; attempt++) {
            try {
                let response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.warn(`Fetch failed (attempt ${attempt + 1} of ${retries}): ${error.message}`);
                if (attempt < retries - 1) {
                    document.getElementById('download-status').innerText = `Koneksi gagal, mencoba lagi (${attempt + 1})...`;
                    await sleep(delay);
                } else {
                    throw new Error("Gagal fetch data setelah beberapa kali mencoba.");
                }
            }
        }
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